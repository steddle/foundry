<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Support\Facades\Route;
use Steddle\Foundry\Http\Middleware\Noindex;

test('a response is marked noindex', function () {
    Route::get('/private', fn () => 'private')->middleware(Noindex::class);

    $this->get('/private')->assertOk()->assertHeader('X-Robots-Tag', 'noindex');
});

test('a guard that throws still answers marked noindex', function () {
    Route::get('/login', fn () => 'login')->name('login');
    Route::get('/dashboard', fn () => throw new AuthenticationException)->middleware(Noindex::class);

    $this->get('/dashboard')->assertRedirect('/login')->assertHeader('X-Robots-Tag', 'noindex');
});

test('ahead of `auth` wherever a route names them, so the redirect to login is marked', function () {
    Route::get('/login', fn () => 'login')->name('login');
    Route::get('/settings', fn () => 'settings')->middleware(['web', Authenticate::class, Noindex::class]);

    $this->get('/settings')->assertRedirect('/login')->assertHeader('X-Robots-Tag', 'noindex');
});
