<?php

use Flux\FluxServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Testing\TestResponse;
use Laravel\Socialite\SocialiteServiceProvider;
use Livewire\LivewireServiceProvider;
use Steddle\Foundry\Concerns\HasSteddleAccount;
use Steddle\Foundry\Tests\TestCase;

beforeAll(function () {
    TestCase::$config = [
        'imprint.account' => ['client' => 'imprint', 'secret' => 'the-secret', 'server' => 'https://account.test', 'home' => '/home'],
        'cache.default' => 'array',
    ];
    TestCase::$providers = [LivewireServiceProvider::class, FluxServiceProvider::class, SocialiteServiceProvider::class];
});

afterAll(function () {
    TestCase::$config = [];
    TestCase::$providers = [];
});

beforeEach(function () {
    config([
        'imprint.name' => 'Imprint',
        'imprint.stylesheet' => null,
        'auth.providers.users.model' => AccountUser::class,
    ]);

    Schema::create('users', function (Blueprint $table): void {
        $table->id();
        $table->string('steddle_id')->nullable()->unique();
        $table->string('name');
        $table->string('email')->unique();
        $table->string('locale')->nullable();
        $table->timestamp('email_verified_at')->nullable();
        $table->rememberToken();
    });

    Schema::create('sessions', function (Blueprint $table): void {
        $table->string('id')->primary();
        $table->foreignId('user_id')->nullable()->index();
        $table->string('ip_address', 45)->nullable();
        $table->text('user_agent')->nullable();
        $table->longText('payload');
        $table->integer('last_activity')->index();
    });

    foreach (glob(dirname(__DIR__).'/vendor/laravel/{passport,sanctum}/database/migrations/*.php', GLOB_BRACE) as $migration) {
        (require $migration)->up();
    }

    Route::middleware(['web', 'auth'])->get('probe', fn () => 'The page asked for.');
    Route::getRoutes()->refreshNameLookups();

    $this->withoutVite();
});

class AccountUser extends Authenticatable
{
    use HasSteddleAccount;

    protected $table = 'users';

    protected $guarded = [];

    public $timestamps = false;
}

/** @param  array<string, mixed>  $user */
function tokenAnswers(array $user = []): void
{
    Http::fake(['account.test/oauth/token' => Http::response([
        'token_type' => 'Bearer',
        'access_token' => 'unused',
        'user' => [...['id' => '01K0ACCOUNT', 'email' => 'ada@imprint.test', 'name' => 'Ada Visser', 'locale' => 'nl'], ...$user],
        'credential' => ['kind' => 'passkey', 'id' => 'credential-hash'],
    ])]);
}

/** @return array<string, string> The authorize request's query, its state among it. */
function authorizeAt(): array
{
    parse_str(parse_url(test()->get(route('login'))->headers->get('Location'), PHP_URL_QUERY), $query);

    return $query;
}

/** postJson() encodes the payload as json_encode() does here, so the signature covers the body it sends. */
function postEvent(array $payload, string $secret = 'the-secret', ?int $at = null): TestResponse
{
    $timestamp = (string) ($at ?? now()->getTimestamp());

    return test()->postJson(route('foundry.account.events'), $payload, [
        'Steddle-Timestamp' => $timestamp,
        'Steddle-Signature' => 'sha256='.hash_hmac('sha256', $timestamp.'.'.json_encode($payload), $secret),
    ]);
}

test('sign-in goes to the account\'s authorize endpoint with Passport\'s parameters, PKCE, the language and the address to fill in', function () {
    app()->setLocale('nl');
    $location = $this->get(route('login', ['email' => 'ada@imprint.test']))->assertRedirect()->headers->get('Location');
    parse_str(parse_url($location, PHP_URL_QUERY), $query);

    expect(strtok($location, '?'))->toBe('https://account.test/oauth/authorize')
        ->and($query)->toMatchArray([
            'client_id' => 'imprint',
            'redirect_uri' => route('foundry.account.callback'),
            'response_type' => 'code',
            'code_challenge_method' => 'S256',
            'ui_locales' => 'nl',
            'login_hint' => 'ada@imprint.test',
        ])
        ->and($query['state'])->toBe(session('state'))
        ->and($query['code_challenge'])->toBe(rtrim(strtr(base64_encode(hash('sha256', session('code_verifier'), true)), '+/', '-_'), '='));
});

test('the callback trades the code with the secret and verifier, links a row by its address, and signs it in remembered with the credential', function () {
    $user = AccountUser::create(['name' => 'ada', 'email' => 'Ada@Imprint.test']);
    tokenAnswers();
    $query = authorizeAt();
    $verifier = session('code_verifier');

    $this->get('/probe')->assertRedirect(route('login'));
    $this->get(route('foundry.account.callback', ['code' => 'the-code', 'state' => $query['state']]))
        ->assertRedirect('/probe')
        ->assertCookie(Auth::guard()->getRecallerName());

    Http::assertSent(fn (ClientRequest $request): bool => $request->url() === 'https://account.test/oauth/token' && $request->data() === [
        'grant_type' => 'authorization_code',
        'client_id' => 'imprint',
        'client_secret' => 'the-secret',
        'code' => 'the-code',
        'redirect_uri' => route('foundry.account.callback'),
        'code_verifier' => $verifier,
    ]);

    expect($user->refresh())->steddle_id->toBe('01K0ACCOUNT')->name->toBe('Ada Visser')->locale->toBe('nl')->email_verified_at->not->toBeNull()
        ->and(session('credential'))->toBe(['kind' => 'passkey', 'link_id' => 'credential-hash']);
    $this->assertAuthenticatedAs($user);
});

test('the remember cookie alone signs the account back in, though it has no password, and no password signs it in', function () {
    tokenAnswers();
    $query = authorizeAt();
    $recaller = Auth::guard()->getRecallerName();
    $cookie = $this->get(route('foundry.account.callback', ['code' => 'the-code', 'state' => $query['state']]))->getCookie($recaller);
    $user = AccountUser::sole();

    $this->flushSession();
    $this->app['auth']->forgetGuards();

    $this->withCookie($recaller, $cookie->getValue())->get('/probe')->assertOk();
    $this->assertAuthenticatedAs($user);
    expect(Auth::guard()->viaRemember())->toBeTrue()
        ->and(Auth::guard()->validate(['email' => 'ada@imprint.test', 'password' => '']))->toBeFalse();
});

test('the migration takes the password and its resets from Laravel\'s stock users table, so a first sign-in makes its row, and puts them back', function () {
    Schema::drop('users');
    Schema::create('users', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->timestamp('email_verified_at')->nullable();
        $table->string('password');
        $table->rememberToken();
    });
    Schema::create('password_reset_tokens', function (Blueprint $table): void {
        $table->string('email')->primary();
        $table->string('token');
        $table->timestamp('created_at')->nullable();
    });
    $migration = require dirname(__DIR__).'/database/migrations/2026_09_25_000002_add_steddle_id_to_users_table.php';
    $migration->up();

    expect(Schema::hasColumn('users', 'password'))->toBeFalse()
        ->and(Schema::hasColumn('users', 'steddle_id'))->toBeTrue()
        ->and(Schema::hasTable('password_reset_tokens'))->toBeFalse();

    tokenAnswers(['id' => '01K0NEW', 'email' => 'cy@imprint.test', 'name' => 'Cy']);
    $query = authorizeAt();
    $this->get(route('foundry.account.callback', ['code' => 'the-code', 'state' => $query['state']]))->assertRedirect();

    $user = AccountUser::sole();
    expect($user->steddle_id)->toBe('01K0NEW')->and($user->getAuthPassword())->toBe('');
    $this->assertAuthenticatedAs($user);

    $migration->down();

    expect(Schema::hasColumn('users', 'password'))->toBeTrue()
        ->and(Schema::hasColumn('users', 'steddle_id'))->toBeFalse()
        ->and(Schema::hasTable('password_reset_tokens'))->toBeTrue();
});

test('the callback finds a linked row by its id whatever its address, and goes home', function () {
    $linked = AccountUser::create(['steddle_id' => '01K0ACCOUNT', 'name' => 'Ada Visser', 'email' => 'old@imprint.test']);
    AccountUser::create(['steddle_id' => '01K0SOMEONE', 'name' => 'Bo', 'email' => 'bo@imprint.test']);
    tokenAnswers();

    $query = authorizeAt();
    $this->get(route('foundry.account.callback', ['code' => 'the-code', 'state' => $query['state']]))->assertRedirect('/home');

    expect($linked->refresh()->email)->toBe('ada@imprint.test');
    $this->assertAuthenticatedAs($linked);
});

test('the callback makes a row for an account the imprint has not met, its address verified', function () {
    AccountUser::create(['steddle_id' => '01K0SOMEONE', 'name' => 'Cy', 'email' => 'cy@imprint.test']);
    tokenAnswers(['id' => '01K0NEW', 'email' => 'cy@elsewhere.test', 'name' => 'Cy']);

    $query = authorizeAt();
    $this->get(route('foundry.account.callback', ['code' => 'the-code', 'state' => $query['state']]));

    expect(AccountUser::firstWhere('steddle_id', '01K0NEW'))->email->toBe('cy@elsewhere.test')->email_verified_at->not->toBeNull();
});

test('a callback with an error, a state not its own or a code the account refuses signs no one in and offers another try', function (Closure $callback) {
    Http::fake(['account.test/oauth/token' => Http::response(['error' => 'invalid_grant'], 400)]);
    $query = authorizeAt();

    $this->get(route('foundry.account.callback', $callback($query)))
        ->assertOk()
        ->assertSee("Sign-in didn't go through")
        ->assertSee('Something went wrong between Imprint and your Steddle account.')
        ->assertSee('Sign in again')
        ->assertDontSee('Try again')
        ->assertSee(route('login'));

    $this->assertGuest();
})->with([
    'an error' => [fn (array $query): array => ['error' => 'server_error', 'state' => $query['state']]],
    'another state' => [fn (array $query): array => ['code' => 'the-code', 'state' => 'forged']],
    'a refused code' => [fn (array $query): array => ['code' => 'the-code', 'state' => $query['state']]],
]);

test('turning the consent down goes back to the page a guest was on, and home from one behind auth', function (?string $intended, string $back) {
    Route::middleware('web')->get('pricing', fn () => 'Pricing.');
    Route::getRoutes()->refreshNameLookups();
    Http::fake();

    $query = authorizeAt();

    if ($intended !== null) {
        session(['url.intended' => url($intended)]);
    }

    $this->get(route('foundry.account.callback', ['error' => 'access_denied', 'state' => $query['state']]))
        ->assertRedirect(url($back));

    $this->assertGuest();
    Http::assertNothingSent();
})->with([
    'a public page' => ['/pricing', '/pricing'],
    'a page behind auth' => ['/probe', '/'],
    'no page' => [null, '/'],
]);

test('the account being unreachable signs no one in', function () {
    Http::fake(fn () => throw new ConnectionException('Down'));
    $query = authorizeAt();

    $this->get(route('foundry.account.callback', ['code' => 'the-code', 'state' => $query['state']]))->assertOk()->assertSee("Sign-in didn't go through");
    $this->assertGuest();
});

test('a signed-in visitor of the sign-in goes home, and signing out is this imprint\'s alone', function () {
    $user = AccountUser::create(['steddle_id' => '01K0ACCOUNT', 'name' => 'Ada Visser', 'email' => 'ada@imprint.test']);
    Http::fake();

    $this->actingAs($user)->get(route('login'))->assertRedirect(url('/home'));
    $this->post(route('logout'))->assertRedirect('/');

    $this->assertGuest();
    Http::assertNothingSent();
});

test('an event with a bad signature, an old timestamp or no secret configured is refused', function (string $secret, int $age, ?string $configured) {
    config(['imprint.account.secret' => $configured]);
    AccountUser::create(['steddle_id' => '01K0ACCOUNT', 'name' => 'Ada Visser', 'email' => 'ada@imprint.test']);

    postEvent(['event' => 'updated', 'id' => '01K0ACCOUNT', 'name' => 'Mallory'], $secret, now()->getTimestamp() - $age)->assertForbidden();

    expect(AccountUser::sole()->name)->toBe('Ada Visser');
})->with([
    'another secret' => ['not-the-secret', 0, 'the-secret'],
    'six minutes old' => ['the-secret', 360, 'the-secret'],
    'no secret' => ['', 0, null],
]);

test('an update fills the row, and an account this imprint has not met answers 200', function () {
    $user = AccountUser::create(['steddle_id' => '01K0ACCOUNT', 'name' => 'Ada Visser', 'email' => 'ada@imprint.test', 'locale' => 'en']);

    postEvent(['event' => 'updated', 'id' => '01K0ACCOUNT', 'email' => 'ada@elsewhere.test', 'name' => 'Ada Jansen', 'locale' => 'nl'])->assertOk();
    postEvent(['event' => 'left', 'id' => '01K0UNKNOWN'])->assertOk();

    expect($user->refresh())->email->toBe('ada@elsewhere.test')->name->toBe('Ada Jansen')->locale->toBe('nl')->steddle_id->toBe('01K0ACCOUNT');
});

test('an address another row holds leaves the row its old one, and the rest of the update lands', function () {
    $user = AccountUser::create(['steddle_id' => '01K0ACCOUNT', 'name' => 'Ada Visser', 'email' => 'ada@imprint.test']);
    AccountUser::create(['name' => 'ada', 'email' => 'Ada@Elsewhere.test']);

    postEvent(['event' => 'updated', 'id' => '01K0ACCOUNT', 'email' => 'ada@elsewhere.test', 'name' => 'Ada Jansen'])->assertOk();

    expect($user->refresh())->email->toBe('ada@imprint.test')->name->toBe('Ada Jansen');
});

test('signing out everywhere drops the account\'s sessions and its remember token, and leaving deletes it', function () {
    config(['session.driver' => 'database']);
    $user = AccountUser::create(['steddle_id' => '01K0ACCOUNT', 'name' => 'Ada Visser', 'email' => 'ada@imprint.test', 'remember_token' => 'remembered']);
    DB::table('sessions')->insert([
        ['id' => 'ada', 'user_id' => $user->id, 'payload' => '', 'last_activity' => 0],
        ['id' => 'bo', 'user_id' => $user->id + 1, 'payload' => '', 'last_activity' => 0],
    ]);

    postEvent(['event' => 'signed-out', 'id' => '01K0ACCOUNT'])->assertOk();

    expect(DB::table('sessions')->pluck('id')->all())->toBe(['bo'])
        ->and($user->refresh()->remember_token)->not->toBe('remembered');

    DB::table('sessions')->insert(['id' => 'ada-again', 'user_id' => $user->id, 'payload' => '', 'last_activity' => 0]);
    postEvent(['event' => 'left', 'id' => '01K0ACCOUNT'])->assertOk();

    expect(AccountUser::count())->toBe(0)
        ->and(DB::table('sessions')->pluck('id')->all())->toBe(['bo']);
});

test('an unknown event is refused', function () {
    postEvent(['event' => 'renamed', 'id' => '01K0ACCOUNT'])->assertStatus(422);
});

test('staff is a verified address on steddle.com', function () {
    $staff = fn (array $attributes): bool => Gate::forUser(new AccountUser([...['name' => 'Ada', 'email_verified_at' => now()], ...$attributes]))->allows('staff');

    expect($staff(['email' => 'ada@steddle.com']))->toBeTrue()
        ->and($staff(['email' => 'Ada@Steddle.com']))->toBeTrue()
        ->and($staff(['email' => 'ada@steddle.com', 'email_verified_at' => null]))->toBeFalse()
        ->and($staff(['email' => 'ada@mail.steddle.com']))->toBeFalse()
        ->and($staff(['email' => 'ada@steddle.com.example']))->toBeFalse()
        ->and(Gate::forUser(null)->allows('staff'))->toBeFalse();
});

test('the settings panel leads to the account\'s page', function () {
    $html = Blade::render('<foundry:steddle-account />');

    expect($html)->toContain('href="https://account.test/account"')->toContain('Manage your Steddle account')->toContain('leave Imprint');
});

test('the privacy part names the imprint and the account\'s host, in English and Dutch', function (string $locale, string $heading) {
    app()->setLocale($locale);

    expect(view('foundry::legal.account')->render())->toContain($heading)->toContain('account.test')->toContain('Imprint');
})->with([['en', 'Your Steddle account'], ['nl', 'Je Steddle-account']]);
