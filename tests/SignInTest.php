<?php

use Flux\FluxServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Laravel\Passkeys\Contracts\PasskeyUser;
use Laravel\Passkeys\PasskeyAuthenticatable;
use Livewire\LivewireServiceProvider;
use Steddle\Foundry\Auth\LoginLink;
use Steddle\Foundry\Auth\MagicLink;
use Steddle\Foundry\FoundryServiceProvider;
use Steddle\Foundry\Tests\TestCase;

beforeAll(function () {
    TestCase::$config = ['imprint.auth' => ['signup' => true], 'cache.default' => 'array'];
    TestCase::$providers = [LivewireServiceProvider::class, FluxServiceProvider::class];
});

afterAll(function () {
    TestCase::$config = [];
    TestCase::$providers = [];
});

beforeEach(function () {
    $this->artisan('view:clear');
    config([
        'imprint.name' => 'Imprint',
        'imprint.stylesheet' => null,
        'fortify.home' => '/home',
        'auth.providers.users.model' => SignInUser::class,
    ]);

    Schema::create('users', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->timestamp('email_verified_at')->nullable();
        $table->rememberToken();
    });

    (require dirname(__DIR__).'/database/migrations/2026_09_25_000001_create_magic_links_table.php')->up();

    // Fortify's login route, which renders the view the foundry hands it.
    Route::middleware(['web', 'guest'])->get('login', fn () => view('foundry::auth.login'))->name('login');
    Route::middleware(['web', 'auth'])->get('probe', fn () => 'The page asked for.');
    Route::getRoutes()->refreshNameLookups();

    Notification::fake();
    $this->withoutVite();
});

afterEach(function () {
    $this->artisan('view:clear');
});

class SignInUser extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';

    protected $guarded = [];

    public $timestamps = false;
}

class SignInPasskeyUser extends SignInUser implements PasskeyUser
{
    use PasskeyAuthenticatable;

    public function getForeignKey(): string
    {
        return 'user_id';
    }
}

function mailedLink(string $email = 'ada@imprint.test'): string
{
    test()->from('/login')->post(route('login.magic.send'), ['email' => $email]);

    $url = null;
    Notification::assertSentTo(SignInUser::firstWhere('email', $email), LoginLink::class, function (LoginLink $notification) use (&$url): bool {
        $url = $notification->url;

        return true;
    });

    return $url;
}

test('the first link to an address makes its account, and the answer is the inbox card', function () {
    $this->from('/login')->post(route('login.magic.send'), ['email' => 'Ada@Imprint.test'])
        ->assertRedirect('/login')
        ->assertSessionHas('sign_in_email', 'ada@imprint.test');

    $user = SignInUser::sole();
    expect($user->email)->toBe('ada@imprint.test')->and($user->name)->toBe('ada');
    Notification::assertSentTo($user, LoginLink::class);

    $this->get('/login')->assertOk()
        ->assertSee('Check your inbox')
        ->assertSee('We sent a sign-in link to <strong class="font-semibold text-zinc-950 dark:text-zinc-50">ada@imprint.test</strong>', false)
        ->assertSee('Use a different email address')
        ->assertDontSee('name="email"', false);
});

test('without signup only an existing account is mailed, and an unknown address gets the same answer', function () {
    config(['imprint.auth.signup' => false]);
    $known = SignInUser::create(['name' => 'Ada Visser', 'email' => 'ada@imprint.test']);

    $unknown = $this->from('/login')->post(route('login.magic.send'), ['email' => 'bo@imprint.test']);
    $unknownFlash = session('sign_in_email');
    $existing = $this->from('/login')->post(route('login.magic.send'), ['email' => 'ada@imprint.test']);

    expect($unknown->status())->toBe($existing->status())
        ->and($unknown->headers->get('Location'))->toBe($existing->headers->get('Location'))
        ->and($unknownFlash)->toBe('bo@imprint.test')
        ->and(SignInUser::count())->toBe(1);

    Notification::assertSentTo($known, LoginLink::class);
    Notification::assertCount(1);

    $this->get('/login')->assertSee('has an account, a sign-in link is on its way', false);
});

test('the login page asks for the address with the browser\'s passkeys offered in the field', function () {
    $this->get('/login')->assertOk()
        ->assertSee('Sign in to Imprint')
        ->assertSee('autocomplete="email webauthn"', false)
        ->assertSee('action="'.route('login.magic.send').'"', false);
});

test('passkey sign-in, where it is routed, keeps the reader signed in', function () {
    Route::get('passkey/login-options', fn () => [])->name('passkey.login-options');
    Route::post('passkey/login', fn () => [])->name('passkey.login');
    Route::getRoutes()->refreshNameLookups();

    $this->get('/login')->assertOk()
        ->assertSee(route('passkey.login'), false)
        ->assertSee('remember: true', false);
});

test('the link\'s page posts itself and spends nothing, with a button for a browser without script', function () {
    $url = mailedLink();

    $this->get($url)->assertOk()
        ->assertSee('Signing you in')
        ->assertSee('animate-spin', false)
        ->assertSee('<form method="POST" action="'.e(Request::create($url)->fullUrl()).'" id="foundry-sign-in"', false)
        ->assertSee("document.getElementById('foundry-sign-in').submit();", false)
        ->assertSeeInOrder(['<noscript>', 'type="submit"', 'Sign in', '</noscript>'], false);

    $this->get($url)->assertOk()->assertSee('Signing you in');

    expect(MagicLink::sole()->used_at)->toBeNull();
    $this->assertGuest();
});

test('the POST signs the account in once, remembered, with its address verified, and goes home', function () {
    $url = mailedLink();

    $response = $this->post($url)->assertRedirect(url('/home'));

    $user = SignInUser::sole();
    $this->assertAuthenticatedAs($user);
    $response->assertCookie(Auth::guard()->getRecallerName());
    expect($user->hasVerifiedEmail())->toBeTrue()
        ->and(MagicLink::sole()->used_at)->not->toBeNull()
        ->and(MagicLink::sole()->used_ip)->toBe('127.0.0.1');

    Auth::logout();

    $this->post($url)->assertRedirect(Request::create($url)->fullUrl());
    $this->assertGuest();
    $this->get($url)->assertOk()->assertSee('This link no longer works')->assertDontSee('foundry-sign-in');
});

test('an expired link says so and signs no one in', function () {
    $url = mailedLink();

    $this->travel(MagicLink::MINUTES + 1)->minutes();

    $this->get($url)->assertOk()->assertSee('This link no longer works')->assertSee('Email me a new link');
    $this->post($url)->assertRedirect(Request::create($url)->fullUrl());
    $this->assertGuest();
});

test('a link with its account or token changed fails its signature', function () {
    $url = mailedLink();

    $this->get(str_replace('/login/magic/1', '/login/magic/2', $url))->assertForbidden();
    $this->post(preg_replace('/token=[^&]+/', 'token=forged', $url))->assertForbidden();
});

test('a link for another account than the one signed in switches to it', function () {
    $other = SignInUser::create(['name' => 'Bo', 'email' => 'bo@imprint.test']);
    $url = mailedLink();

    $this->actingAs($other)->post($url)->assertRedirect(url('/home'));

    $this->assertAuthenticatedAs(SignInUser::firstWhere('email', 'ada@imprint.test'));
});

test('a link opened while another account is signed in asks before it switches', function () {
    $other = SignInUser::create(['name' => 'Bo', 'email' => 'bo@imprint.test']);
    $url = mailedLink();

    $this->actingAs($other)->get($url)
        ->assertOk()
        ->assertSee('Sign in as ada@imprint.test')
        ->assertDontSee("getElementById('foundry-sign-in').submit()", false);

    $this->assertAuthenticatedAs($other);
});

test('the mailed link is rooted on app.url, whatever host the request named', function () {
    config(['app.url' => 'https://imprint.test']);

    $this->withHeaders(['Host' => 'evil.test', 'X-Forwarded-Host' => 'evil.test']);
    $url = mailedLink();

    expect($url)->toStartWith('https://imprint.test/login/magic/');
});

test('the page that sent the reader to sign in travels on the link, and the sign-in goes back to it', function () {
    $this->get('/probe')->assertRedirect(route('login'));

    $url = mailedLink();

    expect(MagicLink::sole()->intended)->toBe(url('/probe'));

    $this->flushSession();
    $this->post($url)->assertRedirect(url('/probe'));
});

class RedirectForPurpose
{
    public function __invoke(MagicLink $link, Request $request): ?RedirectResponse
    {
        return $link->purpose === 'nda' ? redirect('/nda/'.$link->user_id) : null;
    }
}

test('the imprint resolves a redirect for a purpose of its own, and a login link goes home', function () {
    config(['imprint.auth.redirect' => RedirectForPurpose::class]);
    $user = SignInUser::create(['name' => 'Ada Visser', 'email' => 'ada@imprint.test']);

    $this->post(MagicLink::issue($user, 'nda', expiresAt: now()->addDays(7))->url)->assertRedirect('/nda/'.$user->id);

    Auth::logout();
    $this->post(MagicLink::issue($user)->url)->assertRedirect(url('/home'));
});

test('the limiters key a sign-in by address and IP, a link by its account, and a passkey by IP alone', function () {
    $request = fn (array $input, ?string $route = null): Request => tap(Request::create('/', 'POST', $input, server: ['REMOTE_ADDR' => '10.0.0.1']), function (Request $request) use ($route): void {
        if ($route !== null) {
            $request->setRouteResolver(fn () => app('router')->getRoutes()->match(Request::create($route)));
        }
    });

    expect(RateLimiter::limiter('login')($request(['email' => 'Ada@Imprint.test']))->key)->toBe('ada@imprint.test|10.0.0.1')
        ->and(RateLimiter::limiter('magic-link')($request(['email' => 'Ada@Imprint.test']))->key)->toBe('ada@imprint.test|10.0.0.1')
        ->and(RateLimiter::limiter('magic-link')($request([], '/login/magic/7'))->key)->toBe('7|10.0.0.1')
        ->and(RateLimiter::limiter('passkeys')($request(['credential' => ['id' => 'chosen-by-the-client']]))->key)->toBe('10.0.0.1');
});

test('a signed-in visitor of a guest page goes home, not to a dashboard it may not open', function () {
    Route::get('dashboard', fn () => abort(403))->name('dashboard');
    Route::getRoutes()->refreshNameLookups();
    $user = SignInUser::create(['name' => 'Ada Visser', 'email' => 'ada@imprint.test']);

    $this->actingAs($user)->get('/login')->assertRedirect(url('/home'));
    $this->actingAs($user)->post(route('login.magic.send'), ['email' => 'ada@imprint.test'])->assertRedirect(url('/home'));
});

test('the mail carries the link in the reader\'s language, on the foundry\'s greeting', function () {
    $user = SignInUser::create(['name' => 'ada', 'email' => 'ada@imprint.test']);
    $mail = (new LoginLink('https://imprint.test/login/magic/1'))->toMail($user);

    expect($mail->subject)->toBe('Your sign-in link for Imprint')
        ->and($mail->actionUrl)->toBe('https://imprint.test/login/magic/1')
        ->and($mail->greeting)->toBeNull()
        ->and((string) $mail->render())->toContain('Hi there,')->toContain('valid for 15 minutes');

    app()->setLocale('nl');
    expect((new LoginLink('https://imprint.test'))->toMail($user)->subject)->toBe('Je inloglink voor Imprint');

    $this->from('/login')->post(route('login.magic.send'), ['email' => 'ada@imprint.test']);
    Notification::assertSentTo($user, LoginLink::class, fn (LoginLink $notification): bool => $notification->locale === 'nl');
});

test('the imprint names what the mail and the login page sign in to, from the request, or leaves it to the imprint\'s name', function () {
    config(['imprint.auth.client' => ClientFromRequest::class]);
    $user = SignInUser::create(['name' => 'ada', 'email' => 'ada@imprint.test']);

    $this->get('/login?client=Send+NDA&login_hint=ada@imprint.test')->assertOk()
        ->assertSee('<h1', false)->assertSee('Sign in to Send NDA</h1>', false)
        ->assertSee('<meta name="description" content="Sign in to Send NDA with a link by email. No password needed." />', false)
        ->assertSee('<img src="https://sendnda.test/icon-512.png" alt="" width="48" height="48"', false)
        ->assertSee('value="ada@imprint.test"', false);
    $this->get('/login')->assertSee('Sign in to Imprint</h1>', false)->assertDontSee('icon-512.png');

    $this->from('/login?client=Send+NDA')->post(route('login.magic.send', ['client' => 'Send NDA']), ['email' => 'ada@imprint.test']);
    $this->from('/login')->post(route('login.magic.send'), ['email' => 'ada@imprint.test']);

    $sent = [];
    Notification::assertSentTo($user, LoginLink::class, function (LoginLink $notification) use (&$sent, $user): bool {
        $mail = $notification->toMail($user);
        $sent[] = [$mail->subject, $mail->introLines[0]];

        return true;
    });

    expect($sent[0][0])->toBe('Your sign-in link for Send NDA')->and($sent[0][1])->toStartWith('Sign in to Send NDA')
        ->and($sent[1][0])->toBe('Your sign-in link for Imprint');

});

class ClientFromRequest
{
    /** @return array{name: string, icon: ?string}|null */
    public function __invoke(Request $request): ?array
    {
        return $request->query('client') ? ['name' => $request->query('client'), 'icon' => 'https://sendnda.test/icon-512.png', 'email' => $request->query('login_hint')] : null;
    }
}

test('the card sits under the imprint\'s lockup, without a header or the service line, over one line of Terms, Privacy and steddle.com', function () {
    $html = $this->get('/login')->assertOk()->getContent();

    expect($html)->not->toContain('<header')
        ->toContain('aria-label="'.e(__('foundry::nav.home', ['name' => 'Imprint'])).'"')
        ->toContain('<a href="https://steddle.com" class="py-3')
        ->not->toContain('>Terms</a>')
        ->not->toContain('>Privacy</a>')
        ->not->toContain('© '.now()->year);

    File::ensureDirectoryExists(resource_path('views/legal/documents'));
    File::ensureDirectoryExists(resource_path('content'));
    File::put(resource_path('content/legal.php'), "<?php return ['promises' => [], 'audiences' => ['everyone' => ['title' => 'Everyone', 'documents' => ['privacy' => 'Privacy policy', 'terms' => 'Terms of use']]]];");
    File::put(resource_path('views/legal/documents/privacy.blade.php'), '<p>Privacy.</p>');
    Route::get('legal/{document}', fn () => 'A document.')->name('legal.show');
    Route::getRoutes()->refreshNameLookups();

    try {
        expect($this->get('/login')->getContent())
            ->toContain('>Privacy</a>')
            ->toContain('href="'.route('legal.show', 'privacy').'"')
            ->not->toContain('>Terms</a>');
    } finally {
        File::delete([resource_path('content/legal.php'), resource_path('views/legal/documents/privacy.blade.php')]);
    }
});

test('a card takes a footer as a sunken band at its foot, and a card without one has none', function () {
    $card = fn (string $footer): string => Blade::render('<foundry:layouts.auth title="Continue" description="Continue." card><p>The consent.</p>'.$footer.'</foundry:layouts.auth>', deleteCachedView: true);

    expect($card('<x-slot:footer><p>You will continue to sendnda.com.</p></x-slot:footer>'))
        ->toMatch('#<div class="flex flex-col gap-3 rounded-b-lg border-t border-zinc-200 dark:border-zinc-700 bg-zinc-100 dark:bg-zinc-900/50 px-6 py-5 sm:px-9 sm:py-6">\s*<p>You will continue to sendnda.com.</p>#')
        ->and($card(''))->not->toContain('rounded-b-lg');
});

test('a card takes a line under it, in its column on the ground, and a card without one has none', function () {
    $card = fn (string $after): string => Blade::render('<foundry:layouts.auth title="Continue" description="Continue." card><p>The consent.</p>'.$after.'</foundry:layouts.auth>', deleteCachedView: true);

    expect($card('<x-slot:after>You can leave Send NDA at any time in your Steddle account.</x-slot:after>'))
        ->toMatch('#<p class="[^"]*ink w-full max-w-md text-center[^"]*"[^>]*>You can leave Send NDA at any time in your Steddle account.</p>#')
        ->and($card(''))->not->toContain('max-w-md text-center')
        ->and($card('<x-slot:after> </x-slot:after><x-slot:footer> </x-slot:footer>'))->not->toContain('max-w-md text-center')->not->toContain('rounded-b-lg');
});

test('a sign-in for a client takes its palette, lockup, icons, chrome and name, and without one the imprint\'s', function () {
    expect($this->get('/login')->getContent())->toContain('<html lang="en">');

    config(['imprint.palette' => 'steddle']);

    $own = $this->get('/login')->getContent();

    expect($own)->toContain('<html lang="en" data-palette="steddle">')
        ->toContain('<title data-markdown-skip>Sign in | Imprint</title>')
        ->toContain('<link rel="manifest" href="/site.webmanifest">')
        ->toContain('media="(prefers-color-scheme: dark)"')
        ->toContain('<span class="h-8 sm:h-10">Imprint</span>')
        ->not->toContain('<img src="https://sendnda.test/brand/lockup.svg"')
        ->not->toContain('mark-paper.svg');

    config(['imprint.auth.client' => ThemedClient::class]);

    $themed = $this->get('/login')->getContent();

    expect($themed)->toContain('<html lang="en" data-palette="sendnda">')
        ->toContain('<title data-markdown-skip>Sign in | Send NDA</title>')
        ->toContain('<img src="https://sendnda.test/brand/lockup.svg" alt="Send NDA" class="h-8 w-auto sm:h-10">')
        ->toContain('<img src="https://sendnda.test/brand/logos/mark-paper.svg" alt=""')
        ->toContain('<link rel="icon" href="https://sendnda.test/favicon.svg" type="image/svg+xml">')
        ->toContain('<link rel="icon" href="https://sendnda.test/favicon-96x96.png" type="image/png" sizes="96x96">')
        ->toContain('<link rel="apple-touch-icon" href="https://sendnda.test/apple-touch-icon.png">')
        ->toContain('<meta name="theme-color" content="#2b1720">')
        ->not->toContain('site.webmanifest')
        ->not->toContain('<span class="h-8 sm:h-10">Imprint</span>');
});

test('a client that names no palette, lockup, icons or ink leaves the imprint\'s', function () {
    config(['imprint.palette' => 'bron', 'imprint.auth.client' => ClientFromRequest::class]);

    expect($this->get('/login?client=Send+NDA')->getContent())
        ->toContain('<html lang="en" data-palette="bron">')
        ->toContain('<title data-markdown-skip>Sign in | Send NDA</title>')
        ->toContain('<span class="h-8 sm:h-10">Imprint</span>')
        ->toContain('<link rel="manifest" href="/site.webmanifest">');
});

class ThemedClient
{
    /** @return array{name: string, icon: ?string, palette: ?string, lockup: ?string, icons: ?string, ink: ?string} */
    public function __invoke(Request $request): array
    {
        return [
            'name' => 'Send NDA',
            'icon' => 'https://sendnda.test/icon-512.png',
            'palette' => 'sendnda',
            'lockup' => 'https://sendnda.test/brand/lockup.svg',
            'icons' => 'https://sendnda.test',
            'ink' => '#2b1720',
        ];
    }
}

test('the imprint\'s note stands at the foot of the card, and nothing where it has none', function () {
    config(['imprint.auth.note' => SignInNote::class]);

    $this->get('/login')->assertSee('One account for Send NDA, Bron and Fly');

    config(['imprint.auth.note' => null]);

    $this->get('/login')->assertDontSee('One account for');
});

class SignInNote
{
    public function __invoke(Request $request): ?string
    {
        return 'One account for Send NDA, Bron and Fly';
    }
}

test('the mail comes from the client\'s name on the imprint\'s address, and from mail.from without a client', function () {
    config(['mail.from' => ['address' => 'hello@steddle.com', 'name' => 'Steddle']]);
    $user = SignInUser::create(['name' => 'ada', 'email' => 'ada@imprint.test']);

    expect((new LoginLink('https://imprint.test', 'Send NDA'))->toMail($user)->from)->toBe(['hello@steddle.com', 'Send NDA'])
        ->and((new LoginLink('https://imprint.test'))->toMail($user)->from)->toBe([]);
});

test('the mail asks an account without a passkey to add one, and leaves an account with one alone', function () {
    Schema::create('passkeys', function (Blueprint $table): void {
        $table->id();
        $table->foreignId('user_id');
        $table->string('name');
        $table->string('credential_id')->unique();
        $table->json('credential');
        $table->timestamp('last_used_at')->nullable();
        $table->timestamps();
    });
    $user = SignInPasskeyUser::create(['name' => 'Ada', 'email' => 'ada@imprint.test']);

    expect((string) (new LoginLink('https://imprint.test'))->toMail($user)->render())->toContain('Add a passkey in your settings');

    $user->passkeys()->create(['name' => 'Laptop', 'credential_id' => 'id', 'credential' => []]);

    expect((string) (new LoginLink('https://imprint.test'))->toMail($user->fresh())->render())->not->toContain('Add a passkey');
    expect((string) (new LoginLink('https://imprint.test'))->toMail(SignInUser::create(['name' => 'Bo', 'email' => 'bo@imprint.test']))->render())->not->toContain('Add a passkey');
});

test('a link\'s row goes a day after it expires, or after imprint.auth.keep days', function () {
    $user = SignInUser::create(['name' => 'Ada', 'email' => 'ada@imprint.test']);
    $link = MagicLink::issue($user);
    $link->forceFill(['expires_at' => now()->subDays(2)])->save();

    expect((new MagicLink)->prunable()->count())->toBe(1);

    config(['imprint.auth.keep' => 30]);
    expect((new MagicLink)->prunable()->count())->toBe(0);
});

test('password managers find the passkey settings where passkeys and a settings page are routed', function () {
    $this->get('/.well-known/passkey-endpoints')->assertNotFound();

    Route::get('settings', fn () => 'Settings')->name('settings');
    Route::post('passkey/login', fn () => [])->name('passkey.login');
    Route::getRoutes()->refreshNameLookups();

    $this->get('/.well-known/passkey-endpoints')->assertOk()->assertExactJson([
        'enroll' => route('settings'),
        'manage' => route('settings'),
    ]);
});

test('the magic links migration publishes under its own tag, apart from onboarding\'s', function () {
    $signIn = array_keys(ServiceProvider::pathsToPublish(FoundryServiceProvider::class, 'foundry-sign-in'));
    $onboarding = array_keys(ServiceProvider::pathsToPublish(FoundryServiceProvider::class, 'foundry-onboarding'));

    expect(array_map('basename', $signIn))->toBe(['2026_09_25_000001_create_magic_links_table.php'])
        ->and(array_map('basename', $onboarding))->toBe(['2026_09_25_000000_add_onboarded_at_to_users_table.php']);
});
