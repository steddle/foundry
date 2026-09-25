<?php

namespace Steddle\Foundry;

use Closure;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Contracts\View\Factory;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Http\Request;
use Illuminate\Notifications\Channels\MailChannel as LaravelMailChannel;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Laravel\Mcp\Server\Http\Controllers\OAuthRegisterController;
use Laravel\Passport\Passport;
use Livewire\Livewire;
use LogicException;
use Spatie\MarkdownResponse\Middleware\RewriteMarkdownUrls as BaseRewriteMarkdownUrls;
use Steddle\Foundry\Console\RenderBrandAssets;
use Steddle\Foundry\Http\Middleware\BrandOAuthMetadata;
use Steddle\Foundry\Http\Middleware\EnsureUserIsOnboarded;
use Steddle\Foundry\Http\Middleware\FollowPreferredLocale;
use Steddle\Foundry\Http\Middleware\FollowVisitorLanguage;
use Steddle\Foundry\Http\Middleware\Noindex;
use Steddle\Foundry\Http\Middleware\SetLocale;
use Steddle\Foundry\Livewire\Welcome;
use Steddle\Foundry\Mail\MailChannel;
use Steddle\Foundry\Markdown\LeagueDriverWithTables;
use Steddle\Foundry\Markdown\RewriteMarkdownUrls;

class FoundryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->extend('markdown-response.driver.league', fn (): LeagueDriverWithTables => new LeagueDriverWithTables(config('markdown-response.driver_options.league.options', [])));

        $this->app->bind(BaseRewriteMarkdownUrls::class, RewriteMarkdownUrls::class);

        // Laravel reads `errors/` under each of `view.paths` in order, so an imprint's own error page wins.
        $this->app['config']->push('view.paths', dirname(__DIR__).'/resources/fallback');

        // Read in order, so an imprint's resources/views/vendor/mail wins. A cached config holds the path already.
        if (! $this->app->configurationIsCached()) {
            $this->app['config']->push('mail.markdown.paths', dirname(__DIR__).'/resources/views/mail');
        }

        $this->app->bind(LaravelMailChannel::class, MailChannel::class);

        // laravel/boost writes Claude Code's guidelines to AGENTS.md by default, and Claude Code reads CLAUDE.md.
        $this->app['config']->set('boost.agents.claude_code.guidelines_path', $this->app['config']->get('boost.agents.claude_code.guidelines_path', 'CLAUDE.md'));

        // Passport's route group, the consent screen included, takes this list as its middleware.
        if ($this->app['config']->get('imprint.onboarding') && ! in_array(EnsureUserIsOnboarded::class, $this->app['config']->get('passport.middleware', []), true)) {
            $this->app['config']->push('passport.middleware', EnsureUserIsOnboarded::class);
        }

        // In register: Fortify reads these as it boots.
        if ($this->app['config']->get('imprint.auth') && class_exists(Fortify::class)) {
            if (! in_array(Noindex::class, $middleware = $this->app['config']->get('fortify.middleware', ['web']), true)) {
                $this->app['config']->set('fortify.middleware', [...$middleware, Noindex::class]);
            }

            // Fortify's default is no limiter; signIn() defines these.
            foreach (['login', 'passkeys'] as $limiter) {
                $this->app['config']->set("fortify.limiters.{$limiter}", $this->app['config']->get("fortify.limiters.{$limiter}") ?? $limiter);
            }
        }

        // In register: Passport registers the device routes and grant on this flag as it boots, and no imprint holds their table.
        if ($this->app['config']->get('imprint.mcp') && class_exists(Passport::class)) {
            Passport::$deviceCodeGrantEnabled = false;
        }
    }

    public function boot(): void
    {
        $this->tag();

        // In boot, after Livewire merges its defaults under the imprint's config/livewire.php, so this holds over both.
        config(['livewire.make_command.type' => 'mfc', 'livewire.make_command.emoji' => false]);

        // First, so an imprint's file under resources/views/foundry stands in for the foundry's.
        Blade::anonymousComponentPath(resource_path('views/foundry'), 'foundry');

        Blade::anonymousComponentPath(__DIR__.'/../resources/views/foundry', 'foundry');

        Blade::anonymousComponentPath(__DIR__.'/../resources/views/components', 'foundry');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'foundry');

        // An imprint's resources/views/vendor/notifications, then the foundry's, then Laravel's.
        $this->callAfterResolving('view', function (Factory $view): void {
            $view->prependNamespace('notifications', __DIR__.'/../resources/views/notifications');

            foreach (array_reverse(config('view.paths', [])) as $path) {
                if (is_dir($own = $path.'/vendor/notifications')) {
                    $view->prependNamespace('notifications', $own);
                }
            }
        });
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'foundry');

        if ($this->app->runningInConsole()) {
            $this->commands([RenderBrandAssets::class]);
        }

        $this->localize();

        $this->noindexBeforeAuth();

        $this->onboarding();

        $this->signIn();

        $this->account();

        $this->mcp();

        $this->loadRoutesFrom(__DIR__.'/../routes/foundry.php');
    }

    /**
     * Runs before Blade compiles component tags, where a `precompiler` would
     * run after them.
     */
    private function tag(): void
    {
        Blade::prepareStringsForCompilationUsing(fn (string $view): string => str_replace(['<foundry:', '</foundry:'], ['<x-foundry::', '</x-foundry::'], $view));
    }

    /**
     * The priority list names the interface `Authenticate` implements, and an
     * unlisted middleware goes to its end, after `auth`: Noindex must precede
     * it so the redirect to login carries it. Set on the router, not the
     * kernel, whose methods copy its groups over the router's and drop what a
     * package pushed.
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

    private function onboarding(): void
    {
        $migration = '2026_09_25_000000_add_onboarded_at_to_users_table.php';
        $this->publishesMigrations([__DIR__.'/../database/migrations/'.$migration => database_path('migrations/'.$migration)], 'foundry-onboarding');

        if (! config('imprint.onboarding')) {
            return;
        }

        $router = $this->app['router'];

        // After authentication, which sends a guest to log in first.
        if (! in_array(EnsureUserIsOnboarded::class, $router->middlewarePriority, true)) {
            $at = array_search(AuthenticatesRequests::class, $router->middlewarePriority, true);

            array_splice($router->middlewarePriority, $at === false ? count($router->middlewarePriority) : $at + 1, 0, [EnsureUserIsOnboarded::class]);
        }

        // Every route that authenticates, as it or its controller declares it or through a group, before its middleware
        // is gathered: gathering caches the list, and a middleware added to the route after would not reach it.
        Event::listen(RouteMatched::class, function (RouteMatched $event) use ($router): void {
            $declared = [...$event->route->middleware(), ...$event->route->controllerMiddleware()];

            if (in_array(EnsureUserIsOnboarded::class, $declared, true)) {
                return;
            }

            $authenticates = collect($router->resolveMiddleware($declared))
                ->contains(fn (mixed $middleware): bool => is_string($middleware) && is_a(Str::before($middleware, ':'), AuthenticatesRequests::class, true));

            if ($authenticates) {
                $event->route->middleware(EnsureUserIsOnboarded::class);
            }
        });

        Livewire::component('foundry.welcome', Welcome::class);
    }

    private function signIn(): void
    {
        $migration = '2026_09_25_000001_create_magic_links_table.php';
        $this->publishesMigrations([__DIR__.'/../database/migrations/'.$migration => database_path('migrations/'.$migration)], 'foundry-sign-in');

        if (! config('imprint.auth')) {
            return;
        }

        RateLimiter::for('login', fn (Request $request): Limit => Limit::perMinute(5)
            ->by(Str::transliterate(Str::lower((string) $request->input(config('fortify.username', 'email'))).'|'.$request->ip())));

        RateLimiter::for('magic-link', fn (Request $request): Limit => Limit::perMinute(5)
            ->by(($request->route('user') ?? Str::lower((string) $request->input('email'))).'|'.$request->ip()));

        // By IP alone: the credential id is the client's to choose, so keying on it resets the limit.
        RateLimiter::for('passkeys', fn (Request $request): Limit => Limit::perMinute(10)->by((string) $request->ip()));

        // Laravel's default sends to a `dashboard` route, which an account may not be allowed to open.
        RedirectIfAuthenticated::redirectUsing(fn (): string => url(config('fortify.home', '/')));

        if (class_exists(Fortify::class)) {
            Fortify::loginView(fn () => view('foundry::auth.login'));

            // Fortify routes password confirmation whatever its features say, and no account has a password.
            Fortify::confirmPasswordView(fn () => redirect()->route('login'));
        }
    }

    private function account(): void
    {
        $migration = '2026_09_25_000002_add_steddle_id_to_users_table.php';
        $this->publishesMigrations([__DIR__.'/../database/migrations/'.$migration => database_path('migrations/'.$migration)], 'foundry-account');

        Gate::define('staff', fn (Authenticatable $user): bool => $user->email_verified_at !== null && Str::endsWith(Str::lower((string) $user->email), '@steddle.com'));

        if (! config('imprint.account')) {
            return;
        }

        RedirectIfAuthenticated::redirectUsing(fn (): string => url(config('imprint.account.home') ?? '/'));
    }

    /**
     * laravel/mcp registers its OAuth routes from the imprint's routes/ai.php
     * as it boots, before or after the foundry, so they take their middleware
     * as they match.
     */
    private function mcp(): void
    {
        if (! config('imprint.mcp') || ! class_exists(Passport::class)) {
            return;
        }

        Passport::authorizationView('foundry::mcp.authorize');

        RateLimiter::for('api', fn (Request $request): Limit => Limit::perMinute(120)
            ->by($request->user()?->getAuthIdentifier() ?? $request->ip()));

        Event::listen(RouteMatched::class, function (RouteMatched $event): void {
            // The prefix keeps this count apart from the address's other throttled requests.
            if ($event->route->getControllerClass() === OAuthRegisterController::class) {
                $event->route->middleware('throttle:10,60,oauth-register:');
            }

            if ($event->route->named('mcp.oauth.authorization-server', 'mcp.oauth.authorization-server.nested')) {
                $event->route->middleware(BrandOAuthMetadata::class);
            }
        });
    }

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

            // Login fires for the foundry's link, a passkey and a remember cookie alike.
            Event::listen(Login::class, function (Login $event): void {
                $locale = $event->user instanceof HasLocalePreference ? $event->user->preferredLocale() : null;

                if (in_array($locale, Locales::all(), true)) {
                    Cookie::queue(Cookie::forever(Locales::COOKIE, $locale, sameSite: 'lax'));
                }
            });
        }

        // foundry:app.sidebar.group's folds: the browser writes this cookie itself.
        EncryptCookies::except(['foundry_sidebar']);
    }
}
