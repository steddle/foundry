<?php

use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Steddle\Foundry\Events\LocaleChosen;
use Steddle\Foundry\Locales;
use Steddle\Foundry\Tests\TestCase;

beforeAll(function () {
    TestCase::$config = ['imprint.locales' => ['nl' => ['name' => 'Nederlands'], 'en' => ['name' => 'English']]];
});

afterAll(function () {
    TestCase::$config = [];
});

beforeEach(function () {
    Schema::create('users', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->string('locale')->nullable();
        $table->rememberToken();
    });

    Route::get('/', fn () => 'Thuis')->name('nl.home');
    Route::get('en', fn () => 'Home')->name('en.home');
    Route::middleware('web')->group(function (): void {
        Route::post('sign-in/{user}', fn (string $user) => tap('Signed in', fn () => Auth::login(LocaleChoiceUser::findOrFail($user))));
        Route::post('sign-in/plain/{user}', fn (string $user) => tap('Signed in', fn () => Auth::login(LocaleChoicePlainUser::findOrFail($user))));
    });
    Route::getRoutes()->refreshNameLookups();
});

class LocaleChoiceUser extends Authenticatable implements HasLocalePreference
{
    protected $table = 'users';

    protected $guarded = [];

    public $timestamps = false;

    public function preferredLocale(): ?string
    {
        return $this->locale;
    }
}

class LocaleChoicePlainUser extends Authenticatable
{
    protected $table = 'users';

    protected $guarded = [];

    public $timestamps = false;
}

test('a signed-in switch fires LocaleChosen with the user', function () {
    Event::fake([LocaleChosen::class]);
    $user = LocaleChoiceUser::create(['name' => 'Ada', 'email' => 'ada@imprint.test', 'locale' => 'nl']);

    $this->actingAs($user)->post(route('locale.update', 'en'))
        ->assertRedirect(url('en'))
        ->assertPlainCookie(Locales::COOKIE, 'en');

    Event::assertDispatchedTimes(LocaleChosen::class);
    Event::assertDispatched(fn (LocaleChosen $event): bool => $event->locale === 'en' && $event->user?->is($user));
});

test('a guest switch fires LocaleChosen without a user and only sets the cookie', function () {
    Event::fake([LocaleChosen::class]);

    $this->post(route('locale.update', 'en'))
        ->assertRedirect(url('en'))
        ->assertPlainCookie(Locales::COOKIE, 'en');

    Event::assertDispatched(fn (LocaleChosen $event): bool => $event->locale === 'en' && $event->user === null);
    $this->assertGuest();
});

test('signing in sets the locale cookie for good from the user\'s preferred locale', function () {
    $user = LocaleChoiceUser::create(['name' => 'Ada', 'email' => 'ada@imprint.test', 'locale' => 'en']);

    $response = $this->post("sign-in/{$user->id}")->assertOk()->assertPlainCookie(Locales::COOKIE, 'en');

    expect($response->getCookie(Locales::COOKIE, false)->getExpiresTime())->toBeGreaterThan(now()->addYear()->getTimestamp());
});

test('a preferred locale the imprint does not speak sets no cookie', function () {
    $user = LocaleChoiceUser::create(['name' => 'Ada', 'email' => 'ada@imprint.test', 'locale' => 'de']);

    $this->post("sign-in/{$user->id}")->assertOk()->assertCookieMissing(Locales::COOKIE);
});

test('a user without a locale preference sets no cookie on sign-in', function () {
    $user = LocaleChoicePlainUser::create(['name' => 'Ada', 'email' => 'ada@imprint.test', 'locale' => 'en']);

    $this->post("sign-in/plain/{$user->id}")->assertOk()->assertCookieMissing(Locales::COOKIE);
});
