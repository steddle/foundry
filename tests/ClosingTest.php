<?php

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->artisan('view:clear');
    File::ensureDirectoryExists(resource_path('views/foundry'));
    File::put(resource_path('views/foundry/heading.blade.php'), '<h2 {{ $attributes }}>{{ $slot }}</h2>');
    File::put(resource_path('views/foundry/text.blade.php'), '<p {{ $attributes }}>{{ $slot }}</p>');
});

afterEach(function () {
    File::deleteDirectory(resource_path('views/components'));
    File::deleteDirectory(resource_path('views/foundry'));
});

test('a closing centres its title and actions, and leaves out a lede it was not given', function () {
    $html = Blade::render('<foundry:sections.closing title="Last word."><x-slot:actions><a href="#">Act</a></x-slot:actions></foundry:sections.closing>', deleteCachedView: true);

    expect($html)->toContain('items-center text-center')
        ->toContain('Last word.</h2>')
        ->toContain('data-markdown-skip')
        ->toContain('<a href="#">Act</a>')
        ->not->toContain('<p');
});

test('at the start the title, lede and actions stack at the start', function () {
    $html = Blade::render('<foundry:sections.closing align="start" title="Last word." lead="The lede."><x-slot:actions><a href="#">Act</a></x-slot:actions></foundry:sections.closing>', deleteCachedView: true);

    expect($html)->toContain('items-start')
        ->not->toContain('text-center')
        ->toContain('The lede.</p>');
});
