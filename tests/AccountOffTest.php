<?php

use Illuminate\Auth\GenericUser;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

test('an imprint that does not sign in through the Steddle account gets none of its routes, and still the staff gate', function () {
    expect(Route::has('foundry.account.callback'))->toBeFalse()
        ->and(Route::has('foundry.account.events'))->toBeFalse()
        ->and(Gate::has('staff'))->toBeTrue()
        ->and(Gate::forUser(new GenericUser(['id' => 1, 'email' => 'ada@steddle.com', 'email_verified_at' => now()]))->allows('staff'))->toBeTrue();
});
