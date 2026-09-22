<?php

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Steddle\Foundry\Catalog\Catalog;

beforeEach(function () {
    $this->artisan('view:clear');
    File::ensureDirectoryExists(resource_path('views/components/site'));
    File::ensureDirectoryExists(resource_path('views/layouts'));
    File::put(resource_path('views/components/site/heading.blade.php'), '<h2 {{ $attributes }}>{{ $slot }}</h2>');
    File::put(resource_path('views/components/site/text.blade.php'), '<p {{ $attributes }}>{{ $slot }}</p>');
    File::put(resource_path('views/components/site/stamp.blade.php'), <<<'BLADE'
@props(['ink' => 'red', 'label'])
{{--
    The imprint's own stamp,
    in red.
    @group Brand
    @prop ink The colour it is pressed in.
    @slot date The day under the stamp.
    @example Alone
    <x-site.stamp />
--}}
<span>the stamp {{ $label ?? '' }}</span>
BLADE);
    File::ensureDirectoryExists(resource_path('views/components/site/signing'));
    File::put(resource_path('views/components/site/signing/seal.blade.php'), "{{-- @group Elements --}}\n<span>the seal</span>");
    File::put(resource_path('views/layouts/site.blade.php'), '<main>{{ $slot }}</main>');
    Blade::anonymousComponentPath(resource_path('views/layouts'), 'layouts');
});

afterEach(function () {
    File::deleteDirectory(resource_path('views/components'));
    File::deleteDirectory(resource_path('views/layouts'));
});

test('an imprint\'s own component files join the foundry\'s, described and shown by their opening comment', function () {
    $this->get('/components/stamp')->assertOk()
        ->assertSee('The imprint\'s own stamp, in red.')
        ->assertSee('<span>the stamp', false)
        ->assertSee('outside steddle/foundry')
        ->assertSee('href="'.route('foundry.components', 'hero').'"', false)
        ->assertSee('href="'.route('foundry.components', ['from' => 'custom']).'"', false);
});

test('the toggle lists the foundry\'s or the imprint\'s components alone, and the lab links to the custom ones', function () {
    $this->get('/components?from=custom')->assertOk()
        ->assertSee('href="'.route('foundry.components', ['stamp', 'from' => 'custom']).'"', false)
        ->assertDontSee('href="'.route('foundry.components', ['hero', 'from' => 'custom']).'"', false);

    $this->get('/components/hero?from=custom')->assertNotFound();
    $this->get('/components/stamp?from=foundry')->assertNotFound();
    $this->get('/components/container?from=foundry')->assertOk();

    $this->get('/labs')->assertSee('href="'.route('foundry.components', ['from' => 'custom']).'"', false);
});

test('a component without an example shows its tag', function () {
    $this->get('/components/signing-seal')->assertOk()
        ->assertSee('x-site.signing.seal')
        ->assertSee('Add an @example')
        ->assertDontSee('<span>the seal</span>', false);
});

test('a folder that names a group groups its files, and a component in no group of the index is refused', function () {
    File::ensureDirectoryExists(resource_path('views/components/site/sections'));
    File::put(resource_path('views/components/site/sections/pricing.blade.php'), '<section>Pricing</section>');

    expect(Catalog::custom()['sections-pricing']['group'])->toBe('Sections');

    File::put(resource_path('views/components/site/loose.blade.php'), '<p>Loose</p>');

    expect(fn () => Catalog::custom())->toThrow(LogicException::class, 'loose.blade.php names no group of the index');
});

test('a folder with an index is one component, its other files its parts', function () {
    File::ensureDirectoryExists(resource_path('views/components/site/lifecycle'));
    File::put(resource_path('views/components/site/lifecycle/index.blade.php'), "{{-- @group Elements --}}\n<ol>{{ \$slot }}</ol>");
    File::put(resource_path('views/components/site/lifecycle/step.blade.php'), '<li>{{ $slot }}</li>');

    expect(Catalog::custom())->toHaveKey('lifecycle')->not->toHaveKey('lifecycle-step')
        ->and(Catalog::custom()['lifecycle']['group'])->toBe('Elements');
});

test('a file the foundry keeps, or an entry its catalogue names, is no custom component', function () {
    File::put(resource_path('views/components/site/section.blade.php'), '<x-foundry::site.section>{{ $slot }}</x-foundry::site.section>');
    File::put(resource_path('views/components/site/lockup.blade.php'), '<svg></svg>');

    expect(array_keys(Catalog::custom()))->toContain('stamp', 'signing-seal')
        ->not->toContain('section', 'lockup');
});

test('an opening comment is read only where it opens the component', function () {
    File::put(resource_path('views/components/site/late.blade.php'), "@props(['a' => 1])\n\n<p>{{ \$a }}</p>\n{{-- Not the description. @group Elements --}}");

    expect(fn () => Catalog::custom())->toThrow(LogicException::class, 'late.blade.php names no group');
});

test('without component files of its own an imprint has no custom components', function () {
    File::deleteDirectory(resource_path('views/components'));

    expect(Catalog::custom())->toBe([]);
});

test('a component page lists its props with their defaults and its slots, from the file itself', function () {
    $this->get('/components/stamp')->assertOk()
        ->assertSeeInOrder(['Props', 'ink', "'red'", 'The colour it is pressed in.', 'label', 'Required', 'Slots', 'date', 'The day under the stamp.'])
        ->assertDontSee('@prop');
});

test('the index lists every group with each component\'s description', function () {
    $this->get('/components')->assertOk()
        ->assertSee('Sections')
        ->assertSee('href="'.route('foundry.components', 'stamp').'"', false)
        ->assertSee('The imprint\'s own stamp, in red.');
});
