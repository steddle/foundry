<?php

namespace Steddle\Foundry;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Spatie\MarkdownResponse\Middleware\RewriteMarkdownUrls as BaseRewriteMarkdownUrls;
use Steddle\Foundry\Console\RenderBrandAssets;
use Steddle\Foundry\Http\Controllers\RenderBrandAsset;
use Steddle\Foundry\Http\Controllers\ShowComponent;
use Steddle\Foundry\Http\Controllers\ShowLab;
use Steddle\Foundry\Http\Middleware\Noindex;
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

                    Route::view('design', 'design')->name('foundry.design');

                    Route::get('components/{component?}', ShowComponent::class)
                        ->where('component', '[a-z-]+')
                        ->name('foundry.components');
                });
        }
    }
}
