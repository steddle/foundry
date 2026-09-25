<?php

use Flux\FluxServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Laravel\Socialite\SocialiteServiceProvider;
use Livewire\LivewireServiceProvider;
use Steddle\Foundry\Auth\MagicLink;
use Steddle\Foundry\Concerns\HasSteddleAccount;
use Steddle\Foundry\Tests\TestCase;

beforeAll(function () {
    TestCase::$config = [
        'imprint.account' => ['client' => 'imprint', 'secret' => 'the-secret', 'server' => 'https://auth.test'],
        'imprint.auth' => ['redirect' => LinkPurposeRedirect::class],
        'cache.default' => 'array',
    ];
    TestCase::$providers = [LivewireServiceProvider::class, FluxServiceProvider::class, SocialiteServiceProvider::class];
});

afterAll(function () {
    TestCase::$config = [];
    TestCase::$providers = [];
});

beforeEach(function () {
    config(['imprint.name' => 'Imprint', 'imprint.stylesheet' => null, 'auth.providers.users.model' => LinkUser::class]);

    Schema::create('users', function (Blueprint $table): void {
        $table->id();
        $table->string('steddle_id')->nullable()->unique();
        $table->string('name');
        $table->string('email')->unique();
        $table->timestamp('email_verified_at')->nullable();
        $table->rememberToken();
    });

    (require dirname(__DIR__).'/database/migrations/2026_09_25_000001_create_magic_links_table.php')->up();

    $this->withoutVite();
});

class LinkUser extends Authenticatable
{
    use HasSteddleAccount;

    protected $table = 'users';

    protected $guarded = [];

    public $timestamps = false;
}

class LinkPurposeRedirect
{
    public function __invoke(MagicLink $link, Request $request): ?RedirectResponse
    {
        return $link->purpose === 'nda' ? redirect('/nda/signed') : null;
    }
}

test('beside the Steddle account the imprint sends no sign-in links and answers no passkey endpoints, and login is the account\'s', function () {
    expect(Route::has('login.magic.send'))->toBeFalse()
        ->and(Route::has('well-known.passkeys'))->toBeFalse()
        ->and(Route::has('login.magic'))->toBeTrue()
        ->and(Route::has('login.magic.consume'))->toBeTrue()
        ->and(RateLimiter::limiter('magic-link'))->not->toBeNull()
        ->and(RateLimiter::limiter('passkeys'))->toBeNull();

    expect(strtok($this->get(route('login'))->headers->get('Location'), '?'))->toBe('https://auth.test/oauth/authorize');
});

test('the imprint\'s own link still signs its account in, remembered, and goes where imprint.auth.redirect sends it', function () {
    $user = LinkUser::create(['steddle_id' => '01K0ACCOUNT', 'name' => 'Ada Visser', 'email' => 'ada@imprint.test']);
    $url = MagicLink::issue($user, 'nda', expiresAt: now()->addDays(7))->url;

    $this->get($url)->assertOk()->assertSee('foundry-sign-in');
    $this->post($url)->assertRedirect('/nda/signed')->assertCookie(auth()->guard()->getRecallerName());

    $this->assertAuthenticatedAs($user);
});
