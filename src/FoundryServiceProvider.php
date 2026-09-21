<?php

namespace Steddle\Foundry;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Spatie\MarkdownResponse\Middleware\RewriteMarkdownUrls as BaseRewriteMarkdownUrls;
use Steddle\Foundry\Console\RenderBrandAssets;
use Steddle\Foundry\Http\Controllers\RenderBrandAsset;
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

        if ($this->app->runningInConsole()) {
            $this->commands([RenderBrandAssets::class]);
        }

        // The page Chrome renders a brand asset from. Off a public deployment's
        // route list altogether, as the design page is.
        if (! $this->app->isProduction()) {
            Route::get('foundry/brand/{asset}', RenderBrandAsset::class)
                ->middleware(Noindex::class)
                ->name('foundry.brand');
        }
    }
}
