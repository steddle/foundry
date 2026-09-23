<?php

namespace Steddle\Foundry\Testing;

use Closure;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Route;
use Steddle\Foundry\Boost\Skill;
use Steddle\Foundry\Catalog\Catalog;
use Steddle\Foundry\Markdown\MarkdownUrl;
use Steddle\Foundry\Pages;

/**
 * The tests every imprint runs for what the foundry gives it, so each site
 * holds the foundry to one standard. A site registers them from one file in
 * its suite:
 *
 *     Imprint::tests(viewer: fn () => User::factory()->create());
 *
 * `viewer` makes the user the lab opens for where `imprint.pages.guard`
 * guards it; without a guard the lab is open and `viewer` is left out.
 * Pest files a test under the file that registered it, so these run as the
 * site's own.
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

        test('the lab, the design page and the components index render', function (string $route) use ($asViewer) {
            Imprint::open($this, $asViewer)->get(route($route))->assertOk()->assertHeader('X-Robots-Tag', 'noindex');
        })->with(['foundry.lab', 'foundry.design', 'foundry.components']);

        test('the lab is closed to a visitor where the imprint guards it', function () {
            $response = $this->get(route('foundry.lab'));

            config('imprint.pages.guard', []) === [] ? $response->assertOk() : $response->assertRedirect();
        });

        test('the lab, the design page and the components are no routes in production', function () {
            $result = Process::path(base_path())->env(['APP_ENV' => 'production'])->run(['php', 'artisan', 'route:list', '--json']);
            $uris = array_column(json_decode($result->output(), true) ?? [], 'uri');

            expect($uris)->not->toBeEmpty()
                ->not->toContain('labs/{page?}')
                ->not->toContain('design')
                ->not->toContain('components/{component?}');
        });

        test('every public page renders without noindex, and answers markdown', function () {
            foreach ((new Pages)->byLocale() as $pages) {
                foreach ($pages as $page) {
                    expect($this->get($page['url'])->assertOk()->headers->has('X-Robots-Tag'))->toBeFalse();

                    $this->get(MarkdownUrl::of($page['url']))->assertOk()->assertHeader('Content-Type', 'text/markdown; charset=UTF-8');
                }
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
        });
    }

    /**
     * The test signed in as the viewer where the lab asks for one. Public
     * because Pest binds each test's closure to the test case, so the
     * closure no longer sees this class's private members.
     *
     * @param  Closure(): ?Authenticatable  $viewer
     */
    public static function open(object $test, Closure $viewer): object
    {
        $user = config('imprint.pages.guard', []) === [] ? null : $viewer();

        return $user ? $test->actingAs($user) : $test;
    }

    /**
     * Every `bare` example, as [slug, index].
     *
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
