<?php

use Flux\FluxServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Laravel\Fortify\Features;
use Laravel\Fortify\FortifyServiceProvider;
use Laravel\Passkeys\PasskeysServiceProvider;
use Livewire\LivewireServiceProvider;
use Steddle\Foundry\Http\Middleware\Noindex;
use Steddle\Foundry\Tests\TestCase;

beforeAll(function () {
    TestCase::$config = [
        'imprint.auth' => ['signup' => true],
        'fortify.home' => '/home',
        'cache.default' => 'array',
        'fortify.features' => [Features::passkeys()],
        'fortify-options.passkeys' => ['confirmPassword' => false],
    ];
    TestCase::$providers = [LivewireServiceProvider::class, FluxServiceProvider::class, PasskeysServiceProvider::class, FortifyServiceProvider::class];
});

afterAll(function () {
    TestCase::$config = [];
    TestCase::$providers = [];
});

beforeEach(function () {
    $this->artisan('view:clear');
    config(['imprint.name' => 'Imprint', 'imprint.stylesheet' => null, 'auth.providers.users.model' => FortifyUser::class]);

    Schema::create('users', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('email');
        $table->rememberToken();
    });

    $this->withoutVite();
});

afterEach(function () {
    $this->artisan('view:clear');
});

class FortifyUser extends Authenticatable
{
    protected $table = 'users';

    protected $guarded = [];

    public $timestamps = false;
}

test('Fortify\'s login route renders the foundry\'s view, kept out of search', function () {
    $this->get('/login')->assertOk()
        ->assertSee('Sign in to Imprint')
        ->assertSee('action="'.route('login.magic.send').'"', false)
        ->assertSee(route('passkey.login'), false)
        ->assertHeader('X-Robots-Tag', 'noindex');

    expect(config('fortify.middleware'))->toBe(['web', Noindex::class]);
});

test('password confirmation goes to login, since no account has a password', function () {
    $this->actingAs(FortifyUser::create(['name' => 'Ada Visser', 'email' => 'ada@imprint.test']))
        ->get(route('password.confirm'))
        ->assertRedirect(route('login'));
});

test('Fortify\'s login and passkey routes throttle on the foundry\'s limiters, a passkey by IP alone', function () {
    expect(Route::getRoutes()->getByName('login.store')->gatherMiddleware())->toContain('throttle:login')
        ->and(Route::getRoutes()->getByName('passkey.login')->gatherMiddleware())->toContain('throttle:passkeys')
        ->and(Route::getRoutes()->getByName('passkey.login-options')->gatherMiddleware())->toContain('throttle:passkeys');

    $request = Request::create('/passkeys/login', 'POST', ['credential' => ['id' => 'chosen-by-the-client']], server: ['REMOTE_ADDR' => '10.0.0.1']);

    expect(RateLimiter::limiter('passkeys')($request)->key)->toBe('10.0.0.1');
});

test('a signed-in visitor of Fortify\'s guest pages goes to fortify.home', function () {
    $user = FortifyUser::create(['name' => 'Ada Visser', 'email' => 'ada@imprint.test']);

    $this->actingAs($user)->get('/login')->assertRedirect(url('/home'));
    $this->actingAs($user)->get(route('passkey.login-options'))->assertRedirect(url('/home'));
});
