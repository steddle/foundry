<?php

use Illuminate\Auth\GenericUser;
use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    $this->artisan('view:clear');
    File::ensureDirectoryExists(resource_path('views/layouts'));
    File::put(resource_path('views/layouts/site.blade.php'), '<foundry:header /><main>{{ $slot }}</main>');
    Blade::anonymousComponentPath(resource_path('views/layouts'), 'layouts');
    // The icons /design shows are drawn from the mark, on a 32 by 32 grid.
    File::put(resource_path('views/foundry/mark.blade.php'), '<svg viewBox="0 0 32 32" {{ $attributes }}><path fill="currentColor" d="M4 4h24v24H4z" /></svg>');
    config([
        'imprint.name' => 'Imprint',
        'imprint.stylesheet' => null,
        'imprint.ink' => '#0b231c',
        'imprint.paper' => '#f1f2ea',
        'imprint.og' => ['heading' => 'What the imprint is for.', 'lede' => 'One line under it.'],
        'imprint.banner' => ['heading' => 'What the imprint is for.', 'lede' => 'One line under it.'],
    ]);
});

afterEach(function () {
    File::deleteDirectory(resource_path('views/layouts'));
});

/**
 * @param  list<string>  $production
 * @param  list<string>  $guard
 */
function serveIn(string $env, array $production = [], array $guard = ['auth']): void
{
    app()->instance('env', $env);
    config(['imprint.pages.production' => $production, 'imprint.pages.guard' => $guard]);

    Route::setRoutes(new RouteCollection);
    require __DIR__.'/../routes/foundry.php';
    Route::get('login', fn () => 'Log in')->name('login');
    Route::getRoutes()->refreshNameLookups();
}

test('in production an imprint that names design serves it to a signed-in reader, sends a guest to log in, and serves nothing else', function () {
    serveIn('production', ['design']);

    $this->get('/design')->assertRedirect(route('login'));

    $this->actingAs(new GenericUser(['id' => 1]))->get('/design')->assertOk()->assertHeader('X-Robots-Tag', 'noindex')
        ->assertDontSee('href="'.url('/labs').'"', false)
        ->assertDontSee('href="'.url('/components').'"', false);

    foreach (['/labs', '/components', '/foundry/mail', '/foundry/brand/og-image'] as $page) {
        $this->actingAs(new GenericUser(['id' => 1]))->get($page)->assertNotFound();
    }
});

test('in production nothing is served by default, nor what is named where no guard keeps it', function () {
    serveIn('production');

    expect(Route::has('foundry.design'))->toBeFalse()->and(Route::has('foundry.lab'))->toBeFalse();

    serveIn('production', ['design', 'labs'], guard: []);

    expect(Route::has('foundry.design'))->toBeFalse()->and(Route::has('foundry.lab'))->toBeFalse();
});

test('outside production every page is served, whatever pages.production names', function () {
    serveIn('staging', ['design']);

    expect(collect(['foundry.lab', 'foundry.design', 'foundry.components', 'foundry.components.example', 'foundry.mail', 'foundry.brand'])->every(fn (string $name): bool => Route::has($name)))->toBeTrue();
});

test('in production the lab and its pages link only to the pages the imprint serves', function () {
    serveIn('production', ['labs', 'design']);

    $this->actingAs(new GenericUser(['id' => 1]))->get('/labs')->assertOk()
        ->assertSee('href="'.route('foundry.design').'"', false)
        ->assertDontSee('Components</a>', false)
        ->assertDontSee('title="Mail"', false);
});
