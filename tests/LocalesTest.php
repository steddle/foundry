<?php

use Illuminate\Support\Facades\Route;

test('an imprint in one language names its pages without a locale, and localized_route follows', function () {
    config()->set('imprint.locales', ['en' => ['name' => 'English']]);
    Route::localized(fn (Closure $path) => Route::get($path('docs'), fn () => 'docs')->name('docs.index'));
    app('router')->getRoutes()->refreshNameLookups();

    expect(localized_route('docs.index'))->toBe(url('docs'));
});

test('an imprint in several languages names each page per locale, the root language at the root', function () {
    config()->set('imprint.locales', ['nl' => ['name' => 'Nederlands'], 'en' => ['name' => 'English']]);
    Route::localized(fn (Closure $path) => Route::get($path('docs'), fn () => 'docs')->name('docs.index'));
    app('router')->getRoutes()->refreshNameLookups();

    expect(localized_route('docs.index', locale: 'nl'))->toBe(url('docs'))
        ->and(localized_route('docs.index', locale: 'en'))->toBe(url('en/docs'))
        ->and(Route::getRoutes()->getByName('en.docs.index')->getAction('locale'))->toBe('en');
});
