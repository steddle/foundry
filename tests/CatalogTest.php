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

test('the index groups the entries in the order they are listed', function () {
    expect(array_keys(Catalog::groups()))->toBe(['Layout', 'Type', 'Actions', 'Forms', 'Brand', 'Bands', 'Images'])
        ->and(Catalog::groups()['Actions'])->toBe(['button', 'badge', 'actions', 'copy-menu']);
});

test('a component the catalog does not hold is not found', function () {
    $this->get('/components/nothing-here')->assertNotFound();
});
