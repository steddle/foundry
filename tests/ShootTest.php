<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;

beforeEach(function () {
    config(['auth.providers.users.model' => ShootUser::class]);

    Schema::create('users', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('email');
        $table->rememberToken();
    });
});

class ShootUser extends Authenticatable
{
    protected $table = 'users';

    protected $guarded = [];

    public $timestamps = false;
}

function routesIn(string $env): void
{
    app()->instance('env', $env);
    Route::setRoutes(new RouteCollection);
    require __DIR__.'/../routes/foundry.php';
    Route::getRoutes()->refreshNameLookups();
}

test('bin/shoot signs an account in through a signed route, remembered, on any root the browser reaches', function () {
    routesIn('local');
    $user = ShootUser::create(['name' => 'Ada Visser', 'email' => 'ada@imprint.test']);

    $this->get('http://127.0.0.1:8000'.URL::temporarySignedRoute('foundry.shoot', now()->addMinutes(5), ['user' => $user->id], absolute: false))
        ->assertRedirect('/')
        ->assertCookie(auth()->guard()->getRecallerName());

    $this->assertAuthenticatedAs($user);
});

test('the route refuses an unsigned request', function () {
    routesIn('local');
    ShootUser::create(['name' => 'Ada Visser', 'email' => 'ada@imprint.test']);

    $this->withoutExceptionHandling()->get('/foundry/shoot/1');
})->throws(InvalidSignatureException::class);

test('the route is no route outside local', function () {
    foreach (['production', 'staging', 'testing'] as $env) {
        routesIn($env);

        expect(Route::has('foundry.shoot'))->toBeFalse();
    }
});
