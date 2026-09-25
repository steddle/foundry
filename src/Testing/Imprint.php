<?php

namespace Steddle\Foundry\Testing;

use Closure;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Laravel\Passport\Client;
use Laravel\Passport\Contracts\AuthorizationViewResponse;
use Laravel\Passport\Passport;
use Livewire\Livewire;
use Steddle\Foundry\Account;
use Steddle\Foundry\Auth\LoginLink;
use Steddle\Foundry\Auth\MagicLink;
use Steddle\Foundry\Boost\Skill;
use Steddle\Foundry\Catalog\Catalog;
use Steddle\Foundry\Livewire\Welcome;
use Steddle\Foundry\Mail\Greeting;
use Steddle\Foundry\Mail\MailOptions;
use Steddle\Foundry\Markdown\MarkdownUrl;
use Steddle\Foundry\Pages;

/**
 * Registered from one file in an imprint's suite:
 *
 *     Imprint::tests(viewer: fn () => User::factory()->create());
 *
 * `viewer` is the user the lab opens for where `imprint.pages.guard` guards it.
 */
final class Imprint
{
    /**
     * @param  (Closure(): Authenticatable)|null  $viewer
     */
    public static function tests(?Closure $viewer = null): void
    {
        $asViewer = fn (): ?Authenticatable => $viewer?->__invoke();

        test('the published images are rendered from the copy the imprint states', function () {
            $this->artisan('foundry:assets --check')->assertSuccessful();
        });

        test('the foundry skill states the imprint as it stands', function () {
            $installed = Skill::installed();

            expect($installed)->not->toBeEmpty('The foundry skill is not installed: run `php artisan boost:update`.');

            foreach ($installed as $path => $fingerprint) {
                expect($fingerprint)->toBe(Skill::fingerprint(), "{$path} was rendered before the components or the config moved: run `php artisan boost:update`.");
            }
        });

        test('every component in the catalogue renders', function (string $slug) use ($asViewer) {
            Imprint::open($this, $asViewer)->get(route('foundry.components', $slug))->assertOk();
        })->with(array_keys(Catalog::all()));

        test('every example framed at a viewport\'s width renders on a page of its own', function (string $slug, int $index) use ($asViewer) {
            Imprint::open($this, $asViewer)->get(route('foundry.components.example', [$slug, $index]))->assertOk();
        })->with(self::frames());

        test('the lab, the design page, the components index and the sample mail render', function (string $route) use ($asViewer) {
            Imprint::open($this, $asViewer)->get(route($route))->assertOk()->assertHeader('X-Robots-Tag', 'noindex');
        })->with(['foundry.lab', 'foundry.design', 'foundry.components', 'foundry.mail']);

        test('the lab is closed to a visitor where the imprint guards it', function () {
            $response = $this->get(route('foundry.lab'));

            config('imprint.pages.guard', []) === [] ? $response->assertOk() : $response->assertRedirect();
        });

        test('the lab, the design page, the components and the mail are no routes in production, but for those pages.production names behind a guard', function () {
            $result = Process::path(base_path())->env(['APP_ENV' => 'production'])->run(['php', 'artisan', 'route:list', '--json']);
            $uris = array_column(json_decode($result->output(), true) ?? [], 'uri');
            $served = config('imprint.pages.guard', []) === [] ? [] : config('imprint.pages.production', []);

            expect($uris)->not->toBeEmpty();

            foreach (['labs' => 'labs/{page?}', 'design' => 'design', 'components' => 'components/{component?}', 'mail' => 'foundry/mail'] as $page => $uri) {
                in_array($page, $served, true) ? expect($uris)->toContain($uri) : expect($uris)->not->toContain($uri);
            }
        });

        test('a notification greets its recipient by first name, or without a name where they gave none', function () {
            expect(Greeting::for((object) ['name' => 'Ada Visser', 'email' => 'ada@example.com']))->toBe(__('foundry::mail.greeting', ['name' => 'Ada']))
                ->and(Greeting::for((object) ['name' => 'ada', 'email' => 'ada@example.com']))->toBe(__('foundry::mail.greeting_unnamed'))
                ->and(Greeting::for(null))->toBe(__('foundry::mail.greeting_unnamed'));
        });

        test('an account named after its address is sent to /welcome from a signed-in page and the consent screen, and comes back where it was headed', function () {
            if (! config('imprint.onboarding')) {
                $this->markTestSkipped('The imprint does not onboard.');
            }

            // Straight back, past any step of the imprint's own after the name.
            config(['imprint.onboarding.next' => null]);
            $user = Imprint::unnamed();
            Route::middleware(['web', 'auth'])->get('foundry-onboarding-probe', fn () => 'The page asked for.');

            $this->actingAs($user)->get('/foundry-onboarding-probe')->assertRedirect(route('foundry.welcome'));
            Livewire::actingAs($user)->test(Welcome::class)->set('name', 'Ada Visser')->call('save')->assertRedirect(url('/foundry-onboarding-probe'));
            $this->actingAs($user->refresh())->get('/foundry-onboarding-probe')->assertOk();

            if (Route::has('passport.authorizations.authorize')) {
                $this->actingAs(Imprint::unnamed())->get(route('passport.authorizations.authorize'))->assertRedirect(route('foundry.welcome'));
            }
        });

        test('sign-in mails a link that signs its account in once, remembered, from the foundry\'s login page', function () {
            if (! config('imprint.auth')) {
                $this->markTestSkipped('The imprint does not sign in by email.');
            }

            Notification::fake();
            $user = config('auth.providers.users.model')::factory()->create();

            $this->get(route('login'))->assertOk()->assertSee('autocomplete="email webauthn"', false);
            $this->from(route('login'))->post(route('login.magic.send'), ['email' => $user->email])->assertRedirect(route('login'));
            $this->get(route('login'))->assertSee(__('foundry::sign-in.sent.heading'));

            $url = null;
            Notification::assertSentTo($user, LoginLink::class, function (LoginLink $notification) use (&$url): bool {
                $url = $notification->url;

                return true;
            });

            $this->get($url)->assertOk()->assertSee('foundry-sign-in');
            $this->post($url)->assertRedirect()->assertCookie(auth()->guard()->getRecallerName());
            $this->assertAuthenticatedAs($user);
        });

        test('sign-in goes through the Steddle account and back, linking the account by its address, remembered, with the credential it used', function () {
            if (! config('imprint.account')) {
                $this->markTestSkipped('The imprint does not sign in through the Steddle account.');
            }

            $user = config('auth.providers.users.model')::factory()->create(['steddle_id' => null]);
            Http::fake([Account::server('oauth/token') => Http::response([
                'token_type' => 'Bearer',
                'access_token' => 'unused',
                'user' => ['id' => 'foundry-test-account', 'email' => $user->email, 'name' => $user->name, 'locale' => null],
                'credential' => ['kind' => 'magic_link', 'id' => 'foundry-test-credential'],
            ])]);

            $location = $this->get(route('login'))->assertRedirect()->headers->get('Location');
            parse_str((string) parse_url($location, PHP_URL_QUERY), $query);

            expect($location)->toStartWith(Account::server('oauth/authorize').'?')
                ->and($query)->toMatchArray(['client_id' => config('imprint.account.client'), 'redirect_uri' => route('foundry.account.callback'), 'code_challenge_method' => 'S256']);

            $this->get(route('foundry.account.callback', ['code' => 'foundry-test-code', 'state' => $query['state']]))
                ->assertRedirect()
                ->assertCookie(auth()->guard()->getRecallerName());

            $this->assertAuthenticatedAs($user);
            expect($user->refresh()->steddle_id)->toBe('foundry-test-account')
                ->and(session('credential'))->toBe(['kind' => 'magic_link', 'link_id' => 'foundry-test-credential']);
        });

        test('a remembered sign-in comes back from the cookie alone, and no password signs the account in', function () {
            if (! config('imprint.auth') && ! config('imprint.account')) {
                $this->markTestSkipped('The imprint signs no one in.');
            }

            $model = config('auth.providers.users.model');

            if (config('imprint.account')) {
                $user = $model::factory()->create(['steddle_id' => null]);
                Http::fake([Account::server('oauth/token') => Http::response([
                    'token_type' => 'Bearer',
                    'access_token' => 'unused',
                    'user' => ['id' => 'foundry-test-account', 'email' => $user->email, 'name' => $user->name, 'locale' => null],
                    'credential' => ['kind' => 'passkey', 'id' => 'foundry-test-credential'],
                ])]);
                parse_str((string) parse_url($this->get(route('login'))->headers->get('Location'), PHP_URL_QUERY), $query);
                $response = $this->get(route('foundry.account.callback', ['code' => 'foundry-test-code', 'state' => $query['state']]));
            } else {
                $user = $model::factory()->create();
                $response = $this->post(MagicLink::issue($user)->url);
            }

            $recaller = auth()->guard()->getRecallerName();
            $cookie = $response->assertCookie($recaller)->getCookie($recaller);

            $this->flushSession();
            $this->app['auth']->forgetGuards();
            Route::middleware(['web', 'auth'])->get('foundry-recall-probe', fn () => 'Recalled.');

            $this->withCookie($recaller, $cookie->getValue())->get('/foundry-recall-probe')->assertOk();
            $this->assertAuthenticatedAs($user);
            expect(auth()->guard()->validate(['email' => $user->email, 'password' => '']))->toBeFalse();
        });

        test('the Steddle account\'s events are refused unsigned, and fill, sign out and delete the account', function () {
            if (! config('imprint.account')) {
                $this->markTestSkipped('The imprint does not sign in through the Steddle account.');
            }

            $user = config('auth.providers.users.model')::factory()->create(['steddle_id' => 'foundry-test-account', 'remember_token' => 'foundry-test-token']);
            $send = function (array $payload, ?string $secret = null) {
                $timestamp = (string) now()->getTimestamp();
                $signature = hash_hmac('sha256', $timestamp.'.'.json_encode($payload), $secret ?? (string) config('imprint.account.secret'));

                return $this->postJson(route('foundry.account.events'), $payload, ['Steddle-Timestamp' => $timestamp, 'Steddle-Signature' => 'sha256='.$signature]);
            };

            $send(['event' => 'updated', 'id' => 'foundry-test-account', 'name' => 'Mallory'], 'not-the-secret')->assertForbidden();
            $send(['event' => 'updated', 'id' => 'foundry-test-account', 'name' => 'Ada Visser'])->assertOk();
            expect($user->refresh()->name)->toBe('Ada Visser');

            $send(['event' => 'signed-out', 'id' => 'foundry-test-account'])->assertOk();
            expect($user->refresh()->remember_token)->not->toBe('foundry-test-token');

            $send(['event' => 'left', 'id' => 'foundry-test-account'])->assertOk();
            expect($user->fresh())->toBeNull();
        });

        test('staff is a verified address on steddle.com', function () {
            $model = config('auth.providers.users.model');

            expect(Gate::forUser($model::factory()->make(['email' => 'foundry-test@steddle.com', 'email_verified_at' => now()]))->allows('staff'))->toBeTrue()
                ->and(Gate::forUser($model::factory()->make(['email' => 'foundry-test@steddle.com', 'email_verified_at' => null]))->allows('staff'))->toBeFalse()
                ->and(Gate::forUser($model::factory()->make(['email' => 'foundry-test@example.com', 'email_verified_at' => now()]))->allows('staff'))->toBeFalse();
        });

        test('an agent connects through the foundry\'s consent screen, with the device grant off, registration throttled and the icon in the metadata', function () {
            if (! config('imprint.mcp') || ! class_exists(Passport::class)) {
                $this->markTestSkipped('The imprint has no MCP server on Passport.');
            }

            $consent = app(AuthorizationViewResponse::class)->withParameters([
                'client' => new Client(['name' => 'Claude Code (foundry)', 'redirect_uris' => ['http://localhost:33418/callback']]),
                'user' => (object) ['name' => 'Ada Visser', 'email' => 'ada@example.com'],
                'scopes' => [],
                'request' => request(),
                'authToken' => 'foundry-auth-token',
            ])->toResponse(request())->getContent();

            expect($consent)->toContain(e(__('foundry::mcp.consent.heading', ['client' => 'Claude Code', 'name' => config('imprint.name')])))
                ->toContain('<strong class="font-semibold text-zinc-950 dark:text-zinc-50">localhost</strong>')
                ->and(Route::has('passport.device'))->toBeFalse();

            if (Route::has('mcp.oauth.authorization-server')) {
                $this->postJson('/oauth/register', [])->assertHeader('X-RateLimit-Limit', '10');
                $this->getJson('/.well-known/oauth-authorization-server')->assertJsonPath('op_logo_uri', asset('icon-512.png'));
            }
        });

        test('a mail renders through the foundry\'s theme, under the imprint\'s name and over its footer', function () {
            $options = MailOptions::resolve();
            $html = (string) (new MailMessage)->greeting('Dear Ada,')->line('A line only this test writes.')->action('Open', url('/'))->render();

            // The inliner writes quotes and apostrophes in text as themselves, so the words are compared unescaped.
            $text = html_entity_decode($html, ENT_QUOTES);

            expect($text)->toContain('A line only this test writes.')->toContain(config('imprint.name'));

            if ($options['footer'] !== null) {
                expect($text)->toContain($options['footer']);
            }
        });

        test('every public page renders without noindex, and answers markdown', function () {
            foreach ((new Pages)->byLocale() as $pages) {
                foreach ($pages as $page) {
                    expect($this->get($page['url'])->assertOk()->headers->has('X-Robots-Tag'))->toBeFalse();

                    $this->get(MarkdownUrl::of($page['url']))->assertOk()->assertHeader('Content-Type', 'text/markdown; charset=UTF-8');
                }
            }
        })->skip(fn (): bool => ! config('imprint.sitemap'), 'The imprint lists no public pages.');

        test('every view compiles, as php artisan optimize caches them on a deploy', function () {
            try {
                $this->artisan('view:cache')->assertSuccessful();
            } finally {
                $this->artisan('view:clear');
            }
        });

        test('every error page renders in the imprint\'s words', function (int $code) {
            Route::get('foundry-error-probe', fn () => abort($code));

            $this->get('foundry-error-probe')->assertStatus($code)->assertSee(__('foundry::errors.label', ['code' => $code]));
        })->with([403, 404, 419, 429, 500, 503]);

        test('the sitemap names every public page, and llms.txt and llms-full.txt answer', function () {
            $sitemap = $this->get('/sitemap.xml')->assertOk();

            foreach ((new Pages)->byLocale() as $pages) {
                foreach ($pages as $page) {
                    $sitemap->assertSee(e($page['url']), false);
                }
            }

            Cache::forget('llms-full.txt');
            $this->get('/llms.txt')->assertOk()->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
            $this->get('/llms-full.txt')->assertOk()->assertHeader('Content-Type', 'text/plain; charset=UTF-8');

            expect(Cache::has('llms-full.txt'))->toBeTrue();
        })->skip(fn (): bool => ! config('imprint.sitemap'), 'The imprint lists no public pages.');
    }

    /**
     * Public: Pest binds each test's closure to the test case, where this
     * class's private members are out of reach.
     *
     * @param  Closure(): ?Authenticatable  $viewer
     */
    public static function open(object $test, Closure $viewer): object
    {
        $user = config('imprint.pages.guard', []) === [] ? null : $viewer();

        return $user ? $test->actingAs($user) : $test;
    }

    /** Public for the reason open() is. */
    public static function unnamed(): Authenticatable
    {
        $user = config('auth.providers.users.model')::factory()->create();
        $user->forceFill(['name' => Str::before($user->email, '@'), 'onboarded_at' => null])->save();

        return $user;
    }

    /**
     * @return list<array{0: string, 1: int}>
     */
    private static function frames(): array
    {
        return collect(Catalog::all())->flatMap(fn (array $entry, string $slug): array => collect($entry['examples'])
            ->filter(fn (array $example): bool => ($example['ground'] ?? null) === 'bare' && ! ($example['code'] ?? false))
            ->keys()
            ->map(fn (int $index): array => [$slug, $index])
            ->all())->values()->all();
    }
}
