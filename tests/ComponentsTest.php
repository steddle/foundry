<?php

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;

test('a site resolves the foundry components under x-site', function () {
    $html = Blade::render('<x-site.section id="band">Body</x-site.section>');

    expect($html)
        ->toContain('<section class="scroll-mt-4')
        ->toContain('id="band"')
        ->toContain('mx-auto w-full max-w-wide')
        ->toContain('Body');
});

test('a site overrides a foundry component by keeping a file of the same name', function () {
    $directory = resource_path('views/components/site');
    File::ensureDirectoryExists($directory);
    File::put($directory.'/section.blade.php', '<section>the site\'s own</section>');

    try {
        expect(Blade::render('<x-site.section>Body</x-site.section>'))->toBe('<section>the site\'s own</section>');
    } finally {
        File::deleteDirectory(resource_path('views/components'));
    }
});
