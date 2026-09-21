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
    // Both tests write the same file within a second, which Blade's compiled
    // view check reads as unchanged.
    $this->artisan('view:clear');
    $directory = resource_path('views/components/site');
    File::ensureDirectoryExists($directory);
    File::put($directory.'/section.blade.php', '<section>the site\'s own</section>');

    try {
        expect(Blade::render('<x-site.section>Body</x-site.section>', deleteCachedView: true))->toBe('<section>the site\'s own</section>');
    } finally {
        File::deleteDirectory(resource_path('views/components'));
    }
});

test('a site\'s own version of a component can wrap the foundry\'s', function () {
    // Both tests write the same file within a second, which Blade's compiled
    // view check reads as unchanged.
    $this->artisan('view:clear');
    $directory = resource_path('views/components/site');
    File::ensureDirectoryExists($directory);
    File::put($directory.'/section.blade.php', '<x-foundry::site.section {{ $attributes }}>{{ $slot }} and the site\'s own</x-foundry::site.section>');

    try {
        expect(Blade::render('<x-site.section id="band">Body</x-site.section>', deleteCachedView: true))
            ->toContain('id="band"')
            ->toContain('mx-auto w-full max-w-wide')
            ->toContain('Body')
            ->toContain('and the site\'s own');
    } finally {
        File::deleteDirectory(resource_path('views/components'));
    }
});
