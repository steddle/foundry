<?php

namespace Steddle\Foundry;

use Closure;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use LogicException;
use Spatie\MarkdownResponse\Middleware\RewriteMarkdownUrls as BaseRewriteMarkdownUrls;
use Steddle\Foundry\Console\RenderBrandAssets;
use Steddle\Foundry\Http\Middleware\FollowPreferredLocale;
use Steddle\Foundry\Http\Middleware\FollowVisitorLanguage;
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

        $this->loadRoutesFrom(__DIR__.'/../routes/foundry.php');
    }

    /**
     * `Route::localized()` registers the pages its callback names once per
     * language, named `{locale}.{page}`, the root language at the root; an
     * imprint in one language registers them once, named as they are.
     * `Route::docs()` and `Route::legal()` register the pages `Content`
     * publishes, inside it or on their own. A site with more than one language
     * also gets the visitor's language on a page that states none, and a plain
     * cookie for the switch; routes/foundry.php adds the switch's route and
     * the root language's prefix sent to the root.
     */
    private function localize(): void
    {
        Route::macro('localized', function (Closure $pages): void {
            if (! Locales::multilingual()) {
                $pages(fn (string $english): string => '/'.ltrim($english, '/'), Locales::root());

                return;
            }

            foreach (Locales::all() as $locale) {
                // `locale` on the group reaches every route's action, where `Locales` reads it.
                Route::group([
                    'as' => "{$locale}.",
                    'locale' => $locale,
                    'middleware' => ["locale:{$locale}", ...($locale === Locales::root() ? [FollowVisitorLanguage::class] : [])],
                ], fn () => $pages(fn (string $english): string => localized_path($english, $locale) ?: '/', $locale));
            }
        });

        // The docs and the legal documents, one page per entry `Content` publishes.
        Route::macro('docs', function (?Closure $path = null, ?string $locale = null): void {
            $path ??= fn (string $english): string => '/'.$english;

            Route::view($path('docs'), 'foundry::docs.index')->name('docs.index');
            $articles = [];

            foreach (Content::docs($locale ?? Locales::root()) as $topic => $entry) {
                Route::view($path("docs/{$topic}"), 'foundry::docs.category', ['slug' => $topic])->name("docs.category.{$topic}");

                foreach (array_keys($entry['articles']) as $article) {
                    // An article is named by its slug alone, so two topics cannot both hold one.
                    if (isset($articles[$article])) {
                        throw new LogicException("The docs topics {$articles[$article]} and {$topic} both hold the article {$article}; an article's slug names it on its own.");
                    }

                    $articles[$article] = $topic;

                    Route::view($path("docs/{$topic}/{$article}"), 'foundry::docs.article', ['slug' => $article, 'topicSlug' => $topic])->name("docs.article.{$article}");
                }
            }
        });

        Route::macro('legal', function (?Closure $path = null, ?string $locale = null): void {
            $path ??= fn (string $english): string => '/'.$english;
            $documents = collect(Content::legal($locale ?? Locales::root())['audiences'])->flatMap(fn (array $audience): array => array_keys($audience['documents']))->all();

            Route::view($path('legal'), 'foundry::legal.index')->name('legal.index');
            Route::view($path('legal').'/{document}', 'foundry::legal.show')->whereIn('document', $documents)->name('legal.show');
        });

        $router = $this->app['router'];
        $router->aliasMiddleware('locale', SetLocale::class);

        if (class_exists(Livewire::class)) {
            // A component update arrives at Livewire's own endpoint, where the page's route does not run.
            Livewire::addPersistentMiddleware([SetLocale::class]);
        }

        if (Locales::multilingual()) {
            $router->pushMiddlewareToGroup('web', FollowPreferredLocale::class);
            EncryptCookies::except([Locales::COOKIE]);
        }
    }
}
