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

test('a scene draws its photo in every width and format, with its scrim over it', function () {
    $html = Blade::render('<x-site.scene name="stones" scrim="bg-black/50" eager class="object-right" />', deleteCachedView: true);

    expect($html)
        ->toContain('/stones/home-1672.avif 1672w')
        ->toContain('src="/stones/home-1280.webp"')
        ->toContain('fetchpriority="high"')
        ->toContain('object-right')
        ->toContain('-z-10 bg-black/50');
});

test('steps check what is done, mark the current one, number the rest and open a step that has an action', function () {
    $html = Blade::render('<x-site.steps :steps="$steps" />', ['steps' => [
        ['label' => 'You', 'meta' => 'Ada Visser', 'status' => 'complete', 'click' => 'goTo(1)'],
        ['label' => 'Terms', 'status' => 'current'],
        ['label' => 'Sent', 'meta' => 'Both sign online', 'status' => 'incomplete', 'icon' => 'paper-airplane'],
        ['label' => 'Done', 'status' => 'incomplete'],
    ]]);

    expect($html)
        ->toContain('<flux:timeline horizontal')
        ->toContain('wire:click="goTo(1)"')
        ->and(substr_count($html, '<flux:icon.check variant="micro" />'))->toBe(1)
        ->and(substr_count($html, '<flux:icon :icon='))->toBe(1)
        ->and(substr_count($html, 'aria-current="step"'))->toBe(1)
        ->and($html)->toMatch('/\s4\s*<\/flux:timeline.indicator>/')
        ->not->toMatch('/\s3\s*<\/flux:timeline.indicator>/');
});
