<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Steddle\Foundry\Http\Controllers\RootLanguagePrefix;
use Steddle\Foundry\Http\Middleware\FollowVisitorLanguage;
use Steddle\Foundry\Locales;

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

test('a page published in one language names no counterpart in the other', function () {
    config()->set('imprint.locales', ['nl' => ['name' => 'Nederlands'], 'en' => ['name' => 'English']]);
    Route::get('alleen-nl', fn () => (string) Locales::counterpart(request(), 'en'))->name('nl.docs.article.only');
    app('router')->getRoutes()->refreshNameLookups();

    $this->get('alleen-nl')->assertOk()->assertContent('');
});

test('following the visitor\'s language keeps what else a response varies on', function () {
    config()->set('imprint.locales', ['nl' => ['name' => 'Nederlands'], 'en' => ['name' => 'English']]);
    $response = (new FollowVisitorLanguage)->handle(
        Request::create('/', 'GET', server: ['HTTP_ACCEPT_LANGUAGE' => 'nl']),
        fn () => response('markdown')->setVary('Accept'),
    );

    expect($response->getVary())->toBe(['Accept', 'Accept-Language', 'Cookie']);
});

test('the root language\'s prefix is sent to the root on the site, whatever the path holds', function () {
    $redirect = (new RootLanguagePrefix)(Request::create('/nl/x'), "//evil.test/a\r\nSet-Cookie: b");

    expect($redirect->getTargetUrl())->toBe(url('evil.test/aSet-Cookie: b'))
        ->and($redirect->getStatusCode())->toBe(301);
});
