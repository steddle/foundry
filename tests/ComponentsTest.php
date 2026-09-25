<?php

use Flux\FluxServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Livewire\LivewireServiceProvider;

test('a site resolves the foundry components under foundry:', function () {
    $html = Blade::render('<foundry:section id="band">Body</foundry:section>');

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
    $directory = resource_path('views/foundry');
    File::ensureDirectoryExists($directory);
    File::put($directory.'/section.blade.php', '<section>the site\'s own</section>');

    try {
        expect(Blade::render('<foundry:section>Body</foundry:section>', deleteCachedView: true))->toBe('<section>the site\'s own</section>');
    } finally {
        File::deleteDirectory(resource_path('views/components'));
        File::deleteDirectory(resource_path('views/foundry'));
    }
});

test('an imprint\'s own components are Laravel\'s own x- tags, beside the foundry\'s', function () {
    $this->artisan('view:clear');
    $directory = resource_path('views/components');
    File::ensureDirectoryExists($directory);
    File::put($directory.'/seal.blade.php', '<span>the imprint\'s seal</span>');

    try {
        expect(Blade::render('<x-seal /><foundry:section>Body</foundry:section>', deleteCachedView: true))
            ->toContain('the imprint\'s seal')
            ->toContain('mx-auto w-full max-w-wide');
    } finally {
        File::deleteDirectory(resource_path('views/components'));
        File::deleteDirectory(resource_path('views/foundry'));
    }
});

test('a scene draws its photo in every width and format, with its scrim over it', function () {
    $html = Blade::render('<foundry:scene name="stones" scrim="bg-black/50" eager class="object-right" />', deleteCachedView: true);

    expect($html)
        ->toContain('/stones/home-1672.avif 1672w')
        ->toContain('src="/stones/home-1280.webp"')
        ->toContain('fetchpriority="high"')
        ->toContain('object-right')
        ->toContain('-z-10 bg-black/50');
});

test('steps check what is done, mark the current one, number the rest and open a step that has an action', function () {
    $html = Blade::render('<foundry:steps :steps="$steps" />', ['steps' => [
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

test('a ghost button reads on bone and on ink, bordered only at base with a label, and takes Flux\'s sizes and an icon alone', function () {
    $this->app->register(LivewireServiceProvider::class);
    $this->app->register(FluxServiceProvider::class);

    $labelled = Blade::render('<foundry:button href="#" variant="ghost">Ghost</foundry:button>', deleteCachedView: true);
    $icon = Blade::render('<foundry:button variant="ghost" size="sm" icon="ellipsis-horizontal" aria-label="More" />', deleteCachedView: true);

    expect($labelled)
        ->toContain('text-zinc-950! dark:text-zinc-50!')
        ->toContain('border border-zinc-950/13 dark:border-zinc-50/13')
        ->toContain('h-10')
        ->and($icon)
        ->toContain('h-8 text-sm rounded-md gap-2 w-8')
        ->toContain('data-flux-icon')
        ->toContain('aria-label="More"')
        ->not->toContain('border-zinc-950/13');
});

test('text renders as a div for a line that holds a block', function () {
    $html = Blade::render('<foundry:text variant="small" tone="muted" as="div">Added <div>tooltip</div></foundry:text>');

    expect($html)
        ->toStartWith('<div class="text-small text-zinc-600 dark:text-zinc-400"')
        ->not->toContain('<p');
});
