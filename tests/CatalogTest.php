<?php

use Illuminate\Support\Facades\File;
use Steddle\Foundry\Catalog\Catalog;

test('every entry names itself, its group, where it comes from and one example', function () {
    foreach (Catalog::all() as $slug => $entry) {
        expect($slug)->toMatch('/^[a-z-]+$/')
            ->and($entry['name'])->not->toBeEmpty()
            ->and($entry['from'])->toBeIn(['foundry', 'imprint', 'custom'])
            ->and($entry['examples'])->not->toBeEmpty();

        foreach ($entry['examples'] as $example) {
            expect($example['blade'])->toContain('<foundry:');
        }
    }
});

test('the index keeps its groups in order and each group alphabetical', function () {
    expect(array_keys(Catalog::groups()))->toBe(['Shell', 'Sections', 'Layout', 'Navigation', 'Type', 'Elements', 'Forms', 'Brand', 'Print'])
        ->and(Catalog::groups()['Navigation'])->toBe(['account-menu', 'breadcrumb', 'confirm-item', 'copy-menu', 'link-row', 'pager', 'side-nav'])
        ->and(Catalog::shared()['sections-split']['name'])->toBe('Split');
});

test('a component the catalog does not hold is not found', function () {
    $this->get('/components/nothing-here')->assertNotFound();
});

test('every foundry component the index lists shows at least one example with Blade', function () {
    foreach (Catalog::shared() as $slug => $entry) {
        expect($entry['examples'])->not->toBeEmpty("{$slug} has no example");

        foreach ($entry['examples'] as $example) {
            expect($example['blade'])->not->toBeEmpty("{$slug}: {$example['title']} has no Blade");
        }
    }
});

test('every component the foundry keeps is in the index, its parts with it', function () {
    $components = collect(File::allFiles(__DIR__.'/../resources/views/foundry'))
        ->map(fn ($file): string => str_replace(['/index.blade.php', '.blade.php'], '', $file->getRelativePathname()))
        ->reject(fn (string $path): bool => str_contains($path, '/') && is_file(__DIR__.'/../resources/views/foundry/'.dirname($path).'/index.blade.php'))
        ->map(fn (string $path): string => str_replace('/', '-', $path))
        ->sort()->values()->all();

    expect(array_keys(Catalog::shared()))->toContain(...$components);
});

test('the lockup, the mark and the bar an imprint supplies are no stand-in, a copy of another component is', function () {
    File::put(resource_path('views/foundry/bar.blade.php'), '<nav {{ $attributes }}></nav>');
    File::put(resource_path('views/foundry/heading.blade.php'), '<h2 {{ $attributes }}>{{ $slot }}</h2>');

    try {
        expect(Catalog::held('foundry:lockup'))->toBeFalse()
            ->and(Catalog::held('foundry:mark'))->toBeFalse()
            ->and(Catalog::held('foundry:bar'))->toBeFalse()
            ->and(Catalog::held('foundry:heading'))->toBeTrue();
    } finally {
        File::delete([resource_path('views/foundry/bar.blade.php'), resource_path('views/foundry/heading.blade.php')]);
    }
});
