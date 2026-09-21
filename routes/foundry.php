<?php

use Illuminate\Support\Facades\Route;
use Steddle\Foundry\Http\Controllers\AgentFiles;
use Steddle\Foundry\Http\Controllers\RenderBrandAsset;
use Steddle\Foundry\Http\Controllers\RootLanguagePrefix;
use Steddle\Foundry\Http\Controllers\ShowComponent;
use Steddle\Foundry\Http\Controllers\ShowLab;
use Steddle\Foundry\Http\Controllers\UpdateLocale;
use Steddle\Foundry\Http\Middleware\Noindex;
use Steddle\Foundry\Locales;

// The sitemap and the llms files, for an imprint that lists its pages in `imprint.sitemap`.
if (config('imprint.sitemap')) {
    Route::middleware('web')->group(function (): void {
        Route::get('sitemap.xml', [AgentFiles::class, 'sitemap'])->name('sitemap');
        Route::get('llms.txt', [AgentFiles::class, 'llms'])->name('llms.index');
        Route::get('llms-full.txt', [AgentFiles::class, 'full'])->name('llms.full');
    });
}

if (Locales::multilingual()) {
    Route::middleware('web')->group(function (): void {
        Route::get(Locales::root().'/{path?}', RootLanguagePrefix::class)->where('path', '.*');

        // Outside every language, because it answers whichever one the reader is leaving.
        Route::post('locale/{locale}', UpdateLocale::class)
            ->whereIn('locale', Locales::all())
            ->name('locale.update');
    });
}

// Off a public deployment's route list altogether: the page Playwright renders
// a brand asset from, and the imprint's pages about itself, open locally and
// behind what config/imprint.php names as `pages.guard` elsewhere.
if (! app()->isProduction()) {
    Route::get('foundry/brand/{asset}', RenderBrandAsset::class)
        ->middleware(Noindex::class)
        ->name('foundry.brand');

    Route::middleware(['web', Noindex::class, ...config('imprint.pages.middleware', []), ...(app()->isLocal() ? [] : config('imprint.pages.guard', []))])
        ->group(function (): void {
            Route::get('labs/{page?}', ShowLab::class)
                ->where('page', '[a-z0-9-]+')
                ->name('foundry.lab');

            Route::view('design', 'foundry::design')->name('foundry.design');

            Route::get('components/{component?}', ShowComponent::class)
                ->where('component', '[a-z0-9_-]+')
                ->name('foundry.components');
        });
}
