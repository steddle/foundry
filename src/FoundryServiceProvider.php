<?php

namespace Steddle\Foundry;

use Closure;
use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
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

        // Laravel reads an error page from `errors/` under each of `view.paths`, in order, so
        // the foundry's sit under a path of their own at the end: an imprint's own
        // resources/views/errors/{code}.blade.php stands in for one.
        $this->app['config']->push('view.paths', dirname(__DIR__).'/resources/fallback');
    }

    public function boot(): void
    {
        $this->tag();

        // An imprint's own file under resources/views/foundry stands in for the
        // foundry's component of that name, as a published Flux component does.
        Blade::anonymousComponentPath(resource_path('views/foundry'), 'foundry');

        Blade::anonymousComponentPath(__DIR__.'/../resources/views/foundry', 'foundry');

        // The components the foundry's own pages are built from, which no imprint writes.
        Blade::anonymousComponentPath(__DIR__.'/../resources/views/components', 'foundry');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'foundry');
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'foundry');

        if ($this->app->runningInConsole()) {
            $this->commands([RenderBrandAssets::class]);
        }

        $this->localize();

        $this->noindexBeforeAuth();

        $this->loadRoutesFrom(__DIR__.'/../routes/foundry.php');
    }

    /**
     * `<foundry:button>` is `<x-foundry::button>`. The rewrite runs before
     * Blade compiles the component tags, where a `precompiler` would run after
     * them; Flux copies Laravel's three tag patterns for the same reason.
     */
    private function tag(): void
    {
        Blade::prepareStringsForCompilationUsing(fn (string $view): string => str_replace(['<foundry:', '</foundry:'], ['<x-foundry::', '</x-foundry::'], $view));
    }

    /**
     * The framework's priority list names the interface `Authenticate`
     * implements, not the class, and an unlisted middleware is pushed to the
     * end of it wherever a route puts it. Naming the interface keeps Noindex
     * ahead of `auth`, so the redirect to login carries it. Set on the router,
     * not the kernel: the kernel's own methods copy its middleware groups over
     * the router's, and drop what a package pushed onto them.
     */
    private function noindexBeforeAuth(): void
    {
        $router = $this->app['router'];

        if (in_array(Noindex::class, $router->middlewarePriority, true)) {
            return;
        }

        $at = array_search(AuthenticatesRequests::class, $router->middlewarePriority, true);

        array_splice($router->middlewarePriority, $at === false ? count($router->middlewarePriority) : $at, 0, [Noindex::class]);
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

        // foundry:app.sidebar.group's folds, which the browser writes itself.
        EncryptCookies::except(['foundry_sidebar']);
    }
}
