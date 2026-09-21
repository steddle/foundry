<?php

use Steddle\Foundry\Catalog\Catalog;

test('every entry names itself, its group, where it comes from and one example', function () {
    foreach (Catalog::all() as $slug => $entry) {
        expect($slug)->toMatch('/^[a-z-]+$/')
            ->and($entry['name'])->not->toBeEmpty()
            ->and($entry['from'])->toBeIn(['foundry', 'imprint', 'custom'])
            ->and($entry['examples'])->not->toBeEmpty();

        foreach ($entry['examples'] as $example) {
            expect($example['blade'])->toContain('<x-site.');
        }
    }
});

test('the index keeps its groups in order and each group alphabetical', function () {
    expect(array_keys(Catalog::groups()))->toBe(['Layout', 'Type', 'Actions', 'Forms', 'Brand', 'Bands', 'Images'])
        ->and(Catalog::groups()['Actions'])->toBe(['actions', 'badge', 'button', 'copy-menu']);
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
