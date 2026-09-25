<?php

use Illuminate\Support\Facades\Route;
use Steddle\Foundry\Http\Controllers\AgentFiles;
use Steddle\Foundry\Http\Controllers\MagicLinkController;
use Steddle\Foundry\Http\Controllers\PasskeyEndpoints;
use Steddle\Foundry\Http\Controllers\RenderBrandAsset;
use Steddle\Foundry\Http\Controllers\RootLanguagePrefix;
use Steddle\Foundry\Http\Controllers\ShowComponent;
use Steddle\Foundry\Http\Controllers\ShowExample;
use Steddle\Foundry\Http\Controllers\ShowLab;
use Steddle\Foundry\Http\Controllers\ShowMail;
use Steddle\Foundry\Http\Controllers\UpdateLocale;
use Steddle\Foundry\Http\Middleware\Noindex;
use Steddle\Foundry\Locales;

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

if (config('imprint.onboarding')) {
    Route::middleware(['web', 'auth', Noindex::class])->group(function (): void {
        Route::view('welcome', 'foundry::onboarding.welcome')->name('foundry.welcome');
    });
}

// The GET and the POST are not behind `guest`: a link for another account switches to it.
if (config('imprint.auth')) {
    Route::middleware(['web', Noindex::class, 'throttle:magic-link'])->group(function (): void {
        Route::post('login/magic', [MagicLinkController::class, 'send'])->middleware('guest')->name('login.magic.send');
        Route::get('login/magic/{user}', [MagicLinkController::class, 'confirm'])->middleware('signed:relative')->name('login.magic');
        Route::post('login/magic/{user}', [MagicLinkController::class, 'consume'])->middleware('signed:relative')->name('login.magic.consume');
    });

    Route::get('.well-known/passkey-endpoints', PasskeyEndpoints::class)->name('well-known.passkeys');
}

// The page Playwright renders a brand asset from.
if (! app()->isProduction()) {
    Route::get('foundry/brand/{asset}', RenderBrandAsset::class)
        ->middleware(Noindex::class)
        ->name('foundry.brand');
}

// In production, none where no guard keeps them.
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
