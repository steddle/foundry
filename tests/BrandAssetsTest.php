<?php

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Steddle\Foundry\Brand\BrandAssets;

beforeEach(function () {
    // The components every imprint supplies itself.
    $directory = resource_path('views/components/site');
    File::ensureDirectoryExists($directory);
    File::put($directory.'/lockup.blade.php', '<span {{ $attributes }}>Imprint</span>');
    File::put($directory.'/mark.blade.php', '<svg {{ $attributes }}></svg>');
    File::put($directory.'/marker.blade.php', '<mark>{{ $slot }}</mark>');

    config()->set('imprint', [
        'name' => 'Imprint',
        'stylesheet' => 'resources/css/site.css',
        'og' => ['heading' => 'Dutch law, at the source.', 'marked' => 'at the source.', 'lede' => 'Every article, citable.', 'eyebrow' => 'Legal sources'],
        'banner' => ['heading' => 'Dutch law, at the source.', 'lede' => 'Every article, citable.'],
    ]);
});

afterEach(function () {
    File::deleteDirectory(resource_path('views/components'));
    File::delete(public_path(BrandAssets::MANIFEST));
});

test('every asset is filled from the imprint', function () {
    $assets = BrandAssets::all();

    expect(array_keys($assets))->toBe(['og-image', 'social-preview', 'readme-banner-light', 'readme-banner-dark'])
        ->and($assets['og-image']->markup())
        ->toContain('Dutch law, <mark>at the source.</mark>')
        ->toContain('Legal sources')
        ->and($assets['readme-banner-dark']->markup())->toContain('ink bg-inverse')
        ->and($assets['readme-banner-light']->markup())->toContain('bg-page')->not->toContain('ink');
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

test('the check fails until every image is rendered from the copy the imprint states', function () {
    $this->artisan('foundry:assets --check')->assertFailed();

    File::ensureDirectoryExists(public_path('brand/social'));
    $manifest = [];
    foreach (BrandAssets::all() as $asset) {
        File::put(public_path($asset->path), 'png');
        $manifest[$asset->name] = $asset->digest();
    }
    File::put(public_path(BrandAssets::MANIFEST), json_encode($manifest));

    $this->artisan('foundry:assets --check')->assertSuccessful();

    config()->set('imprint.og.heading', 'Other copy.');

    $this->artisan('foundry:assets --check')->assertFailed();

    File::delete(public_path('og-image.png'));
    File::deleteDirectory(public_path('brand'));
});

test('a copy value may be a translation key, read in the locale its block names', function () {
    app('translator')->addLines(['home.hero.title' => 'Het Nederlandse recht, bij de bron.'], 'nl');
    config()->set('imprint.og.heading', 'home.hero.title');
    config()->set('imprint.og.locale', 'nl');

    expect(BrandAssets::find('og-image')->markup())->toContain('Het Nederlandse recht, bij de bron.');
});

test('the design section shows every image with its size and use', function () {
    $directory = resource_path('views/components/site');
    File::put($directory.'/text.blade.php', '<p {{ $attributes }}>{{ $slot }}</p>');
    File::ensureDirectoryExists(public_path('brand'));
    File::put(public_path(BrandAssets::MANIFEST), json_encode(['og-image' => 'abcdef0123456789']));

    $html = Blade::render('<x-site.brand-assets />', deleteCachedView: true);

    expect($html)
        ->toContain('og-image.png?v=abcdef01')
        ->toContain('github-social-preview.png | 1280×640')
        ->toContain('README &lt;picture&gt;, dark source');

    File::deleteDirectory(public_path('brand'));
});
