<?php

use Flux\FluxServiceProvider;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Livewire\LivewireServiceProvider;
use Steddle\Foundry\Concerns\HasOnboarding;
use Steddle\Foundry\Http\Middleware\EnsureUserIsOnboarded;
use Steddle\Foundry\Livewire\Welcome;
use Steddle\Foundry\Tests\TestCase;

beforeAll(function () {
    TestCase::$config = ['imprint.onboarding' => ['home' => '/home']];
    TestCase::$providers = [LivewireServiceProvider::class, FluxServiceProvider::class];
});

afterAll(function () {
    TestCase::$config = [];
    TestCase::$providers = [];
});

beforeEach(function () {
    $this->artisan('view:clear');
    config(['imprint.name' => 'Imprint', 'imprint.stylesheet' => null]);

    Schema::create('users', function (Blueprint $table): void {
        $table->id();
        $table->string('name')->nullable();
        $table->string('email');
        $table->timestamp('onboarded_at')->nullable();
        $table->rememberToken();
    });

    File::ensureDirectoryExists(resource_path('views/layouts'));
    File::put(resource_path('views/layouts/auth.blade.php'), '@props([\'title\', \'description\'])<main data-title="{{ $title }}">{{ $slot }}</main>');
    Blade::anonymousComponentPath(resource_path('views/layouts'), 'layouts');

    Route::middleware(['web', 'auth'])->get('probe', fn () => 'The page asked for.');
    Route::middleware(['web', 'auth'])->post('probe', fn () => 'Posted.');
    Route::get('login', fn () => 'Log in')->name('login');
    Route::post('logout', fn () => 'Logged out')->middleware(['web', 'auth'])->name('logout');
    Route::getRoutes()->refreshNameLookups();
});

afterEach(function () {
    File::deleteDirectory(resource_path('views/layouts'));
    $this->artisan('view:clear');
});

class OnboardingUser extends Authenticatable
{
    use HasOnboarding;

    protected $table = 'users';

    protected $guarded = [];

    public $timestamps = false;
}

function unnamed(): OnboardingUser
{
    return OnboardingUser::create(['name' => 'ada', 'email' => 'ada@imprint.test']);
}

test('an account named after its address is sent to /welcome from a signed-in page, which it goes back to after', function () {
    $this->actingAs(unnamed())->get('/probe')->assertRedirect(route('foundry.welcome'));

    expect(session('url.intended'))->toBe(url('/probe'));
});

test('a form it posts is sent on too, without being remembered as where to go back to', function () {
    $this->actingAs(unnamed())->post('/probe')->assertRedirect(route('foundry.welcome'));

    expect(session('url.intended'))->toBeNull();
});

test('an account that named itself, a guest and /welcome itself pass', function () {
    $named = OnboardingUser::create(['name' => 'Ada Visser', 'email' => 'ada@imprint.test', 'onboarded_at' => now()]);

    $this->get('/probe')->assertRedirect(route('login'));
    $this->actingAs($named)->get('/probe')->assertOk()->assertSee('The page asked for.');
    $this->actingAs(unnamed())->get('/welcome')->assertOk();
    $this->actingAs(unnamed())->post('/logout')->assertOk();
});

test('a route that authenticates through a group of the imprint\'s own is sent on as well', function () {
    Route::middlewareGroup('app', ['web', Authenticate::class]);
    Route::middleware('app')->get('grouped', fn () => 'Grouped.');

    $this->actingAs(unnamed())->get('/grouped')->assertRedirect(route('foundry.welcome'));
});

test('the check comes after authentication, and Passport\'s routes take it too', function () {
    $priority = app('router')->middlewarePriority;

    expect(array_search(EnsureUserIsOnboarded::class, $priority, true))->toBeGreaterThan(array_search(AuthenticatesRequests::class, $priority, true))
        ->and(config('passport.middleware'))->toContain(EnsureUserIsOnboarded::class);
});

test('/welcome asks for a full name in the imprint\'s sign-in layout, empty where the account carries only its address', function () {
    $this->actingAs(unnamed())->get('/welcome')->assertOk()
        ->assertSee('data-title="Your name"', false)
        ->assertSee('Full name')
        ->assertSee('autocomplete="name"', false)
        ->assertSee('Signed in as ada@imprint.test');

    Livewire::actingAs(unnamed())->test(Welcome::class)->assertSet('name', '');
    Livewire::actingAs(OnboardingUser::create(['name' => 'Ada Visser', 'email' => 'x@imprint.test']))->test(Welcome::class)->assertSet('name', 'Ada Visser');
});

class PrefillFromInvitation
{
    public function __invoke(OnboardingUser $user): ?string
    {
        return 'Ada Visser';
    }
}

test('the imprint can fill the name in from what it knows', function () {
    config(['imprint.onboarding.prefill' => PrefillFromInvitation::class]);

    Livewire::actingAs(unnamed())->test(Welcome::class)->assertSet('name', 'Ada Visser');
});

class LeadFromInvitation
{
    public function __invoke(OnboardingUser $user): ?string
    {
        return $user->email === 'ada@imprint.test' ? 'Bo Jansen sent you an NDA.' : null;
    }
}

test('the imprint can say a sentence of its own before the description, such as who sent the account an NDA', function () {
    config(['imprint.onboarding.lead' => LeadFromInvitation::class]);

    Livewire::actingAs(unnamed())->test(Welcome::class)
        ->assertSet('lead', 'Bo Jansen sent you an NDA.')
        ->assertSee('Bo Jansen sent you an NDA. This is the name Imprint uses for you.');

    Livewire::actingAs(OnboardingUser::create(['name' => 'cy', 'email' => 'cy@imprint.test']))->test(Welcome::class)
        ->assertSet('lead', null)
        ->assertSee('>This is the name Imprint uses for you.', false);
});

test('saving the name marks the account onboarded and goes back where it was headed, or home', function () {
    $user = unnamed();
    session(['url.intended' => url('/probe')]);

    Livewire::actingAs($user)->test(Welcome::class)->set('name', '  Ada Visser ')->call('save')->assertRedirect(url('/probe'));

    expect($user->refresh()->name)->toBe('Ada Visser')->and($user->hasOnboarded())->toBeTrue();

    Livewire::actingAs(unnamed())->test(Welcome::class)->set('name', 'Bo')->call('save')->assertRedirect(url('/home'));
    Livewire::actingAs(unnamed())->test(Welcome::class)->set('name', '')->call('save')->assertHasErrors(['name' => 'required']);
});

test('where the imprint names a next step, saving goes there, and the page the account asked for waits for it', function () {
    Route::get('phone', fn () => 'Phone')->name('onboarding.phone');
    Route::getRoutes()->refreshNameLookups();
    config(['imprint.onboarding.next' => 'onboarding.phone']);
    session(['url.intended' => url('/probe')]);

    Livewire::actingAs(unnamed())->test(Welcome::class)->set('name', 'Ada Visser')->call('save')->assertRedirect(route('onboarding.phone'));

    expect(session('url.intended'))->toBe(url('/probe'));
});

test('an account that has named itself goes straight on from /welcome', function () {
    session(['url.intended' => url('/probe')]);

    Livewire::actingAs(OnboardingUser::create(['name' => 'Ada Visser', 'email' => 'ada@imprint.test', 'onboarded_at' => now()]))
        ->test(Welcome::class)->assertRedirect(url('/probe'));
});

test('an account\'s name is a placeholder when it is empty or the local part of its address', function () {
    expect((new OnboardingUser(['name' => 'Ada', 'email' => 'ada@imprint.test']))->hasPlaceholderName())->toBeTrue()
        ->and((new OnboardingUser(['name' => null, 'email' => 'ada@imprint.test']))->hasPlaceholderName())->toBeTrue()
        ->and((new OnboardingUser(['name' => 'Ada Visser', 'email' => 'ada@imprint.test']))->hasPlaceholderName())->toBeFalse();
});

test('a call with no session, as a token\'s to the API or the MCP, and one that asks for JSON, go through', function () {
    Route::middleware('auth')->get('api/probe', fn () => 'From the API.');

    $this->actingAs(unnamed())->get('/api/probe')->assertOk()->assertSee('From the API.');
    $this->actingAs(unnamed())->getJson('/probe')->assertOk();
});

class GuardedByItsController implements HasMiddleware
{
    public static function middleware(): array
    {
        return ['auth'];
    }

    public function __invoke(): string
    {
        return 'Guarded by its controller.';
    }
}

test('a route whose controller declares auth is sent on too', function () {
    Route::middleware('web')->get('controller-probe', GuardedByItsController::class);

    $this->actingAs(unnamed())->get('/controller-probe')->assertRedirect(route('foundry.welcome'));
});

test('the backfill marks every account with a name of its own onboarded, in chunks, and leaves the rest to /welcome', function () {
    Schema::table('users', fn (Blueprint $table) => $table->dropColumn('onboarded_at'));
    OnboardingUser::insert([
        ['name' => 'Ada Visser', 'email' => 'ada@imprint.test'],
        ['name' => 'bo', 'email' => 'bo@imprint.test'],
        ['name' => null, 'email' => 'cy@imprint.test'],
    ]);

    (require __DIR__.'/../database/migrations/2026_09_25_000000_add_onboarded_at_to_users_table.php')->up();

    expect(OnboardingUser::whereNotNull('onboarded_at')->pluck('email')->all())->toBe(['ada@imprint.test']);
});
