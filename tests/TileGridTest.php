<?php

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->artisan('view:clear');
    $directory = resource_path('views/components/site');
    File::ensureDirectoryExists($directory);
    File::put($directory.'/heading.blade.php', '<h2 {{ $attributes }}>{{ $slot }}</h2>');
    File::put($directory.'/text.blade.php', '<p {{ $attributes }}>{{ $slot }}</p>');
});

afterEach(function () {
    File::deleteDirectory(resource_path('views/components'));
});

test('blank tiles close the last row at three columns and at two', function (int $count, int $wide, int $narrow) {
    $html = Blade::render('<x-site.tile-grid :count="$count"></x-site.tile-grid>', ['count' => $count], deleteCachedView: true);

    expect(substr_count($html, 'max-lg:hidden'))->toBe($wide)
        ->and(substr_count($html, 'max-sm:hidden lg:hidden'))->toBe($narrow);
})->with([
    'a full row' => [3, 0, 1],
    'two of three' => [5, 1, 1],
    'even' => [4, 2, 0],
]);

test('a topic links its title, lists its links and ends on a link to itself', function () {
    $html = Blade::render(<<<'BLADE'
        <x-site.topic icon="command-line" eyebrow="6 articles" title="The CLI" href="/docs/cli" :links="['bron law' => '/docs/cli/law']" more="All 6 articles">Every command.</x-site.topic>
        BLADE, deleteCachedView: true);

    expect($html)
        ->toContain('6 articles')
        ->toContain('<a href="/docs/cli" class="group')
        ->toContain('Every command.')
        ->toContain('<a href="/docs/cli/law"')
        ->toContain('>bron law</a>')
        ->toContain('>All 6 articles</a>');
});

test('a topic without links draws no list', function () {
    $html = Blade::render('<x-site.topic icon="book-open" eyebrow="2" title="Concepts" href="#">Ideas.</x-site.topic>', deleteCachedView: true);

    expect($html)->not->toContain('<ul');
});
