<?php

use Illuminate\Support\Facades\Route;
use Steddle\Foundry\Http\Controllers\AgentFiles;
use Steddle\Foundry\Http\Controllers\RenderBrandAsset;
use Steddle\Foundry\Http\Controllers\RootLanguagePrefix;
use Steddle\Foundry\Http\Controllers\ShowComponent;
use Steddle\Foundry\Http\Controllers\ShowExample;
use Steddle\Foundry\Http\Controllers\ShowLab;
use Steddle\Foundry\Http\Controllers\ShowMail;
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

// Where an account names itself, for an imprint that sets `imprint.onboarding`.
if (config('imprint.onboarding')) {
    Route::middleware(['web', 'auth'])->group(function (): void {
        Route::view('welcome', 'foundry::onboarding.welcome')->name('foundry.welcome');
    });
}

// Off a public deployment's route list: the page Playwright renders a brand asset
// from, kept out of search and nothing more.
if (! app()->isProduction()) {
    Route::get('foundry/brand/{asset}', RenderBrandAsset::class)
        ->middleware(Noindex::class)
        ->name('foundry.brand');
}

// The imprint's pages about itself, the lab, the design page, the components and the mail,
// behind `pages.middleware` and, outside local, `pages.guard` from config/imprint.php. In
// production only those `pages.production` names, and none where no guard keeps them.
$served = match (true) {
    ! app()->isProduction() => ['labs', 'design', 'components', 'mail'],
    config('imprint.pages.guard', []) === [] => [],
    default => config('imprint.pages.production', []),
};

if ($served !== []) {
    Route::middleware(['web', Noindex::class, ...config('imprint.pages.middleware', []), ...(app()->isLocal() ? [] : config('imprint.pages.guard', []))])
        ->group(function () use ($served): void {
            if (in_array('labs', $served, true)) {
                Route::get('labs/{page?}', ShowLab::class)
                    ->where('page', '[a-z0-9-]+')
                    ->name('foundry.lab');
            }

            if (in_array('design', $served, true)) {
                Route::view('design', 'foundry::design')->name('foundry.design');
            }

            if (in_array('mail', $served, true)) {
                Route::get('foundry/mail', ShowMail::class)->name('foundry.mail');
            }

            if (in_array('components', $served, true)) {
                Route::get('components/{component?}', ShowComponent::class)
                    ->where('component', '[a-z0-9_-]+')
                    ->name('foundry.components');

                Route::get('components/{component}/examples/{example}', ShowExample::class)
                    ->where(['component' => '[a-z0-9_-]+', 'example' => '[0-9]+'])
                    ->name('foundry.components.example');
            }
        });
}
