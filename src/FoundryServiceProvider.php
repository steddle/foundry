<?php

namespace Steddle\Foundry;

use Closure;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Spatie\MarkdownResponse\Middleware\RewriteMarkdownUrls as BaseRewriteMarkdownUrls;
use Steddle\Foundry\Console\RenderBrandAssets;
use Steddle\Foundry\Http\Controllers\RenderBrandAsset;
use Steddle\Foundry\Http\Controllers\ShowComponent;
use Steddle\Foundry\Http\Controllers\ShowLab;
use Steddle\Foundry\Http\Controllers\UpdateLocale;
use Steddle\Foundry\Http\Middleware\FollowPreferredLocale;
use Steddle\Foundry\Http\Middleware\FollowVisitorLanguage;
use Steddle\Foundry\Http\Middleware\Noindex;
use Steddle\Foundry\Http\Middleware\SetLocale;
use Steddle\Foundry\Markdown\LeagueDriverWithTables;
use Steddle\Foundry\Markdown\RewriteMarkdownUrls;

class FoundryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->extend('markdown-response.driver.league', fn (): LeagueDriverWithTables => new LeagueDriverWithTables(config('markdown-response.driver_options.league.options', [])));

        $this->app->bind(BaseRewriteMarkdownUrls::class, RewriteMarkdownUrls::class);
    }

    public function boot(): void
    {
        Blade::anonymousComponentPath(__DIR__.'/../resources/views/components');

        // The same components under x-foundry::, so a site's own version of one
        // can wrap the foundry's instead of copying it.
        Blade::anonymousComponentPath(__DIR__.'/../resources/views/components', 'foundry');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'foundry');
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'foundry');

        if ($this->app->runningInConsole()) {
            $this->commands([RenderBrandAssets::class]);
        }

        $this->localize();

        // The page Playwright renders a brand asset from. Off a public deployment's
        // route list altogether, as the design page is.
        if (! $this->app->isProduction()) {
            Route::get('foundry/brand/{asset}', RenderBrandAsset::class)
                ->middleware(Noindex::class)
                ->name('foundry.brand');

            // The imprint's own pages about itself, open locally and behind
            // what config/imprint.php names as `pages.guard` elsewhere.
            Route::middleware(['web', Noindex::class, ...config('imprint.pages.middleware', []), ...($this->app->isLocal() ? [] : config('imprint.pages.guard', []))])
                ->group(function (): void {
                    Route::get('labs/{page?}', ShowLab::class)
                        ->where('page', '[a-z0-9-]+')
                        ->name('foundry.lab');

                    Route::view('design', 'foundry::design')->name('foundry.design');

                    Route::get('components/{component?}', ShowComponent::class)
                        ->where('component', '[a-z-]+')
                        ->name('foundry.components');
                });
        }
    }

    /**
     * `Route::localized()` registers the pages its callback names once per
     * language, named `{locale}.{page}`, the root language at the root. The
     * rest is only for a site with more than one language: the `locale:`
     * middleware, the visitor's language on a page that states none, the
     * switch's route, and the root language's prefix sent to the root.
     */
    private function localize(): void
    {
        Route::macro('localized', function (Closure $pages): void {
            foreach (Locales::all() as $locale) {
                Route::name("{$locale}.")
                    ->middleware(["locale:{$locale}", ...(Locales::multilingual() && $locale === Locales::root() ? [FollowVisitorLanguage::class] : [])])
                    ->group(fn () => $pages(fn (string $english): string => localized_path($english, $locale) ?: '/', $locale));
            }
        });

        $router = $this->app['router'];
        $router->aliasMiddleware('locale', SetLocale::class);

        if (class_exists(Livewire::class)) {
            // A component update arrives at Livewire's own endpoint, where the page's route does not run.
            Livewire::addPersistentMiddleware([SetLocale::class]);
        }

        if (! Locales::multilingual()) {
            return;
        }

        $router->pushMiddlewareToGroup('web', FollowPreferredLocale::class);
        EncryptCookies::except([Locales::COOKIE]);

        Route::middleware('web')->group(function (): void {
            // The root language has no prefix, and an address that names it
            // anyway means the same page. Leading slashes, backslashes and
            // whitespace go first: `/nl//other.example` would otherwise be
            // sent on as `//other.example`, an address on another host.
            Route::get(Locales::root().'/{path?}', function (Request $request, string $path = '') {
                $query = $request->getQueryString();

                return redirect('/'.ltrim($path, "/\\ \t\n\r\0\x0B").($query === null ? '' : "?{$query}"), 301);
            })->where('path', '.*');

            // Outside every language, because it answers whichever one the reader is leaving.
            Route::post('locale/{locale}', UpdateLocale::class)
                ->whereIn('locale', Locales::all())
                ->name('locale.update');
        });
    }
}
