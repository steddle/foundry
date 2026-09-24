<?php

use Illuminate\Auth\GenericUser;
use Illuminate\Support\Facades\Route;
use Steddle\Foundry\Http\Middleware\EnsureUserIsOnboarded;

test('an imprint that does not onboard gets no /welcome, no check on its routes and nothing on Passport\'s', function () {
    expect(Route::has('foundry.welcome'))->toBeFalse()
        ->and(app('router')->middlewarePriority)->not->toContain(EnsureUserIsOnboarded::class)
        ->and(config('passport.middleware', []))->not->toContain(EnsureUserIsOnboarded::class);

    Route::middleware(['web', 'auth'])->get('probe', fn () => 'Probe');
    Route::get('login', fn () => 'Log in')->name('login');
    Route::getRoutes()->refreshNameLookups();

    $this->actingAs(new GenericUser(['id' => 1, 'name' => 'ada', 'email' => 'ada@imprint.test']))->get('/probe')->assertOk();
});
