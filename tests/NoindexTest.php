<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Route;
use Steddle\Foundry\Http\Middleware\Noindex;

test('a response is marked noindex', function () {
    Route::get('/labs', fn () => 'labs')->middleware(Noindex::class);

    $this->get('/labs')->assertOk()->assertHeader('X-Robots-Tag', 'noindex');
});

test('a guard that throws still answers marked noindex', function () {
    Route::get('/login', fn () => 'login')->name('login');
    Route::get('/dashboard', fn () => throw new AuthenticationException)->middleware(Noindex::class);

    $this->get('/dashboard')->assertRedirect('/login')->assertHeader('X-Robots-Tag', 'noindex');
});
