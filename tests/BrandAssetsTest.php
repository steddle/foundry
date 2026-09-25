<?php

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Steddle\Foundry\Brand\BrandAssets;
use Steddle\Foundry\Brand\Icon;

beforeEach(function () {
    $directory = resource_path('views/foundry');
    File::ensureDirectoryExists($directory);
    File::put($directory.'/lockup.blade.php', '<span {{ $attributes }}>Imprint</span>');
    File::put($directory.'/mark.blade.php', '<svg viewBox="0 0 32 32" {{ $attributes }}><path fill="currentColor" d="M1 1h30v30H1Z" /></svg>');

    config()->set('imprint', [
        'name' => 'Imprint',
        'stylesheet' => 'resources/css/site.css',
        'ink' => '#0a212c',
        'paper' => '#ebf3f5',
        'og' => ['heading' => 'Dutch law, at the source.', 'marked' => 'at the source.', 'lede' => 'Every article, citable.', 'eyebrow' => 'Legal sources'],
        'banner' => ['heading' => 'Dutch law, at the source.', 'lede' => 'Every article, citable.'],
    ]);
});

afterEach(function () {
    File::deleteDirectory(resource_path('views/components'));
    File::deleteDirectory(resource_path('views/foundry'));
    File::deleteDirectory(public_path('brand'));
    File::delete([...File::glob(public_path('*.png')), ...File::glob(public_path('*.svg')), public_path('favicon.ico'), public_path('site.webmanifest')]);
});

test('every asset is filled from the imprint', function () {
    $assets = BrandAssets::all();

    expect(array_keys($assets))->toBe(['og-image', 'social-preview', 'readme-banner-light', 'readme-banner-dark', 'favicon-96', 'apple-touch-icon', 'manifest-192', 'icon-512', 'icon-maskable-512', 'mail-logo', 'mail-steddle-logo'])
        ->and($assets['og-image']->markup())
        ->toMatch('/Dutch law, <mark [^>]*>at the source\.<\/mark>/')
        ->toContain('Legal sources')
        ->and($assets['readme-banner-dark']->markup())->toContain('ink bg-zinc-900')
        ->and($assets['readme-banner-light']->markup())->toContain('bg-zinc-50')->not->toContain('ink');
});

test('an imprint without a heading is refused by name', function () {
    config()->set('imprint.banner', ['lede' => 'No heading']);

    BrandAssets::all();
})->throws(InvalidArgumentException::class, 'config/imprint.php states no banner.heading.');

test('the render page sets the asset at its size', function () {
    $this->withoutVite()
        ->get('/foundry/brand/readme-banner-light')
        ->assertOk()
        ->assertHeader('X-Robots-Tag', 'noindex')
        ->assertSee('width: 1600px; height: 520px', false);

    $this->get('/foundry/brand/nothing')->assertNotFound();
});

test('the check fails until every image and file is made from what the imprint states', function () {
    $this->artisan('foundry:assets --check')->assertFailed();

    $manifest = [];
    foreach (BrandAssets::all() as $asset) {
        File::ensureDirectoryExists(dirname(public_path($asset->path)));
        File::put(public_path($asset->path), 'png');
        $manifest[$asset->name] = $asset->digest();
    }
    foreach (BrandAssets::files() as $path => $contents) {
        File::ensureDirectoryExists(dirname(public_path($path)));
        File::put(public_path($path), $contents);
    }
    File::put(public_path('favicon.ico'), 'ico');
    File::put(public_path(BrandAssets::MANIFEST), json_encode($manifest));

    $this->artisan('foundry:assets --check')->assertSuccessful();

    config()->set('imprint.og.heading', 'Other copy.');
    $this->artisan('foundry:assets --check')->assertFailed();

    config()->set('imprint.og.heading', 'Dutch law, at the source.');
    config()->set('imprint.ink', '#000000');
    $this->artisan('foundry:assets --check')->assertFailed();
});

test('a copy value may be a translation key, read in the locale its block names', function () {
    app('translator')->addLines(['home.hero.title' => 'Het Nederlandse recht, bij de bron.'], 'nl');
    config()->set('imprint.og.heading', 'home.hero.title');
    config()->set('imprint.og.locale', 'nl');

    expect(BrandAssets::find('og-image')->markup())->toContain('Het Nederlandse recht, bij de bron.');
});

test('the design section shows every image with its size and use', function () {
    $directory = resource_path('views/foundry');
    File::put($directory.'/text.blade.php', '<p {{ $attributes }}>{{ $slot }}</p>');
    File::ensureDirectoryExists(public_path('brand'));
    File::put(public_path(BrandAssets::MANIFEST), json_encode(['og-image' => 'abcdef0123456789']));

    $html = Blade::render('<foundry:brand-assets />', deleteCachedView: true);

    expect($html)
        ->toContain('og-image.png?v=abcdef01')
        ->toContain('github-social-preview.png | 1280×640')
        ->toContain('README &lt;picture&gt;, dark source');

    File::deleteDirectory(public_path('brand'));
});

test('an imprint that serves MCP names its two server icons', function () {
    config()->set('imprint.mcp_icons', 'bron');

    expect(BrandAssets::find('mcp')->path)->toBe('bron-128.png')
        ->and(BrandAssets::find('mcp-light')->path)->toBe('bron-light-128.png');
});

test('the icons draw the imprint\'s mark in its two colours', function () {
    expect(Icon::svg('tile'))
        ->toContain('<rect width="32" height="32" rx="5" fill="#0a212c"/>')
        ->toContain('<g fill="#ebf3f5" transform="translate(4.16 4.16) scale(0.74)"><path d="M1 1h30v30H1Z" /></g>')
        ->and(Icon::svg('light'))->toContain('fill="#ebf3f5"/><g fill="#0a212c"')
        ->and(Icon::svg('adaptive'))->toContain('@media (prefers-color-scheme:dark){.t{fill:#ebf3f5}.m{fill:#0a212c}}')
        ->and(Icon::svg('full', 180))->toContain('width="180" height="180"')->toContain('translate(8 8) scale(0.5)')->not->toContain('rx=');

    expect(BrandAssets::files()['site.webmanifest'])->toContain('"theme_color": "#0a212c"');
});

test('the design section shows the icons apart, with the files written beside them', function () {
    File::put(resource_path('views/foundry/text.blade.php'), '<p {{ $attributes }}>{{ $slot }}</p>');

    $html = Blade::render('<foundry:brand-assets group="icon" />', deleteCachedView: true);

    expect($html)
        ->toContain('icon-maskable-512.png</span> <span class="whitespace-nowrap">| 512×512')
        ->toContain('favicon.ico')
        ->toContain('site.webmanifest')
        ->not->toContain('og-image.png');
});

test('the head names every icon and the browser chrome in the imprint\'s colours', function () {
    expect(Blade::render('<foundry:favicons />', deleteCachedView: true))
        ->toContain('<link rel="icon" href="/favicon-adaptive.svg" type="image/svg+xml">')
        ->toContain('<meta name="theme-color" content="#ebf3f5" media="(prefers-color-scheme: light)">')
        ->toContain('<meta name="theme-color" content="#0a212c" media="(prefers-color-scheme: dark)">');
});

test('the social preview sets an apostrophe in its words once escaped, bound or written out', function () {
    $words = ['heading' => 'The founders\' agents.', 'lede' => 'Decisions, with an MCP for the founders\' agents.', 'eyebrow' => 'Holly\'s and Mischa\'s'];

    $bound = Blade::render('<foundry:social-preview :heading="$heading" :lede="$lede" :eyebrow="$eyebrow" />', $words, deleteCachedView: true);
    $written = Blade::render('<foundry:social-preview heading="The founders\' agents." lede="Decisions, with an MCP for the founders\' agents." eyebrow="Holly\'s and Mischa\'s" />', deleteCachedView: true);

    foreach ([$bound, $written] as $html) {
        expect($html)
            ->not->toContain('&amp;#039;')
            ->and(html_entity_decode($html, ENT_QUOTES))->toContain('The founders\' agents.')->toContain('the founders\' agents.')->toContain('Holly\'s and Mischa\'s');
    }
});

test('the mail\'s images are the endorsed lockup and Steddle\'s wordmark, on the mail\'s paper, at a path without an @', function () {
    File::put(resource_path('views/foundry/lockup.blade.php'), '@props([\'endorsed\' => false])<span {{ $attributes }}>Imprint{{ $endorsed ? \' BY STEDDLE\' : \'\' }}</span>');
    // Written within the second beforeEach wrote its own, which Blade reads as unchanged.
    $this->artisan('view:clear');
    $assets = BrandAssets::mail();

    expect($assets['mail-logo']->path)->toBe('brand/mail/logo-2x.png')
        ->and([$assets['mail-logo']->width, $assets['mail-logo']->height])->toBe([480, 96])
        ->and($assets['mail-logo']->markup())->toContain('Imprint BY STEDDLE')->toContain('background: #ebf3f5;')->toContain('color: #0a212c;')
        ->and($assets['mail-steddle-logo']->path)->toBe('brand/mail/steddle-logo-2x.png')
        ->and($assets['mail-steddle-logo']->markup())->toContain('>Steddle</div>')->toContain('font-family: var(--font-serif)')
        ->and(collect(BrandAssets::all())->pluck('path')->merge(array_keys(BrandAssets::files()))->filter(fn (string $path): bool => str_contains($path, '@'))->all())->toBe([]);

    config(['imprint.endorsed' => false]);

    expect(array_keys(BrandAssets::mail()))->toBe(['mail-logo'])
        ->and(BrandAssets::mail()['mail-logo']->markup())->toContain('>Imprint</span>');
});
