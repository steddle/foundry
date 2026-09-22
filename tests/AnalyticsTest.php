<?php

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Steddle\Foundry\Http\Middleware\Noindex;

beforeEach(function () {
    $this->artisan('view:clear');
    File::ensureDirectoryExists(resource_path('views/components/site'));
    File::put(resource_path('views/components/site/lockup.blade.php'), '<span {{ $attributes }}>Imprint</span>');
    File::put(resource_path('views/components/site/mark.blade.php'), '<svg {{ $attributes }}></svg>');
    config([
        'imprint.name' => 'Imprint',
        'imprint.stylesheet' => null,
        'imprint.plausible' => 'example.com',
        'imprint.visitors' => 'token-123',
    ]);
    $this->app['env'] = 'production';
});

afterEach(function () {
    File::deleteDirectory(resource_path('views/components'));
});

test('a noindex route renders neither Plausible nor Visitors', function () {
    Route::get('/private', fn () => Blade::render('<x-site.head title="T" description="D" />', deleteCachedView: true))
        ->middleware(Noindex::class);

    $html = $this->get('/private')->getContent();

    expect($html)->not->toContain('plausible.io')->not->toContain('cdn.visitors.now');
});

test('a route without the noindex middleware renders both', function () {
    Route::get('/public', fn () => Blade::render('<x-site.head title="T" description="D" />', deleteCachedView: true));

    $html = $this->get('/public')->getContent();

    expect($html)->toContain('plausible.io')->toContain('cdn.visitors.now');
});
