<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    $this->artisan('view:clear');
    config(['imprint.name' => 'Imprint']);
});

afterEach(function () {
    File::deleteDirectory(resource_path('views/errors'));
    File::deleteDirectory(lang_path('vendor'));
});

test('every error the foundry answers is its own page, with a way home', function (int $code) {
    Route::get('/home', fn () => 'home')->name('home');
    Route::get('/fail', fn () => abort($code));

    $this->get('/fail')->assertStatus($code)
        ->assertSee('Error '.$code)
        ->assertSee(__("foundry::errors.{$code}.title"))
        ->assertSee('Go to Imprint');
})->with([403, 404, 419, 429, 500]);

test('a 503 offers to try again', function () {
    Route::get('/fail', fn () => abort(503));

    $this->get('/fail')->assertStatus(503)->assertSee('<title data-markdown-skip>Back in a minute | Imprint</title>', false)->assertSee('Back in a minute.')->assertSee('Imprint is being updated.')->assertSee('Try again');
});

test('an imprint words an error for itself, and sets it on its scene', function () {
    File::ensureDirectoryExists(lang_path('vendor/foundry/en'));
    File::put(lang_path('vendor/foundry/en/errors.php'), "<?php return ['404' => ['title' => 'Nothing to sign here.']];");
    config(['imprint.errors.scene' => 'stones']);
    Route::get('/fail', fn () => abort(404));

    $this->get('/fail')->assertNotFound()
        ->assertSee('Nothing to sign here.')
        ->assertSee(__('foundry::errors.404.lead'))
        ->assertSee('/stones/home-1280.webp', false);
});

test('an imprint\'s own error page stands in for the foundry\'s', function () {
    File::ensureDirectoryExists(resource_path('views/errors'));
    File::put(resource_path('views/errors/404.blade.php'), '<foundry:layouts.error code="404"><x-slot:actions><a href="/search">Search</a></x-slot:actions></foundry:layouts.error>');
    Route::get('/fail', fn () => abort(404));

    $this->get('/fail')->assertNotFound()->assertSee('<a href="/search">Search</a>', false)->assertDontSee('Go to Imprint');
});
