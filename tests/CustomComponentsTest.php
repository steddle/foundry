<?php

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Once;
use Steddle\Foundry\Catalog\Catalog;

beforeEach(function () {
    $this->artisan('view:clear');
    File::ensureDirectoryExists(resource_path('views/components'));
    File::ensureDirectoryExists(resource_path('views/foundry'));
    File::ensureDirectoryExists(resource_path('views/layouts'));
    File::put(resource_path('views/foundry/heading.blade.php'), '<h2 {{ $attributes }}>{{ $slot }}</h2>');
    File::put(resource_path('views/foundry/text.blade.php'), '<p {{ $attributes }}>{{ $slot }}</p>');
    File::put(resource_path('views/components/stamp.blade.php'), <<<'BLADE'
@props(['ink' => 'red', 'label'])
{{--
    The imprint's own stamp,
    in red.
    @group Brand
    @prop ink The colour it is pressed in.
    @slot date The day under the stamp.
    @example Alone
    <x-stamp />
--}}
<span>the stamp {{ $label ?? '' }}</span>
BLADE);
    File::ensureDirectoryExists(resource_path('views/components/signing'));
    File::put(resource_path('views/components/signing/seal.blade.php'), "{{-- @group Elements --}}\n<span>the seal</span>");
    File::put(resource_path('views/layouts/site.blade.php'), '<main>{{ $slot }}</main>');
    Blade::anonymousComponentPath(resource_path('views/layouts'), 'layouts');
});

afterEach(function () {
    File::deleteDirectory(resource_path('views/components'));
    File::deleteDirectory(resource_path('views/foundry'));
    File::deleteDirectory(resource_path('views/layouts'));
});

test('an imprint\'s own component files join the foundry\'s, described and shown by their opening comment', function () {
    $this->get('/components/stamp')->assertOk()
        ->assertSee('The imprint\'s own stamp, in red.')
        ->assertSee('<span>the stamp', false)
        ->assertSee('outside steddle/foundry')
        ->assertSee('href="'.route('foundry.components', 'sections-hero').'"', false)
        ->assertSee('href="'.route('foundry.components', ['from' => 'custom']).'"', false);
});

test('the toggle lists the foundry\'s or the imprint\'s components alone, and the lab links to the custom ones', function () {
    $this->get('/components?from=custom')->assertOk()
        ->assertSee('href="'.route('foundry.components', ['stamp', 'from' => 'custom']).'"', false)
        ->assertDontSee('href="'.route('foundry.components', ['sections-hero', 'from' => 'custom']).'"', false);

    $this->get('/components/sections-hero?from=custom')->assertNotFound();
    $this->get('/components/stamp?from=foundry')->assertNotFound();
    $this->get('/components/container?from=foundry')->assertOk();

    $this->get('/labs')->assertSee('href="'.route('foundry.components', ['from' => 'custom']).'"', false);
});

test('a component without an example shows its tag', function () {
    $this->get('/components/signing-seal')->assertOk()
        ->assertSee('x-signing.seal')
        ->assertSee('Add an @example')
        ->assertDontSee('<span>the seal</span>', false);
});

test('a folder that names a group groups its files, and a component in no group of the index is refused', function () {
    File::ensureDirectoryExists(resource_path('views/components/sections'));
    File::put(resource_path('views/components/sections/pricing.blade.php'), '<section>Pricing</section>');

    expect(Catalog::custom()['sections-pricing']['group'])->toBe('Sections');

    File::put(resource_path('views/components/loose.blade.php'), '<p>Loose</p>');
    Once::flush();

    expect(fn () => Catalog::custom())->toThrow(LogicException::class, 'loose.blade.php names no group of the index');
});

test('a folder with an index is one component, its other files its parts', function () {
    File::ensureDirectoryExists(resource_path('views/components/lifecycle'));
    File::put(resource_path('views/components/lifecycle/index.blade.php'), "{{-- @group Elements --}}\n<ol>{{ \$slot }}</ol>");
    File::put(resource_path('views/components/lifecycle/step.blade.php'), '<li>{{ $slot }}</li>');

    expect(Catalog::custom())->toHaveKey('lifecycle')->not->toHaveKey('lifecycle-step')
        ->and(Catalog::custom()['lifecycle']['group'])->toBe('Elements');
});

test('a file the foundry keeps, or an entry its catalogue names, is no custom component', function () {
    File::put(resource_path('views/foundry/section.blade.php'), '<x-foundry::section>{{ $slot }}</x-foundry::section>');
    File::put(resource_path('views/foundry/lockup.blade.php'), '<svg></svg>');

    expect(array_keys(Catalog::custom()))->toContain('stamp', 'signing-seal')
        ->not->toContain('section', 'lockup');
});

test('an opening comment is read only where it opens the component', function () {
    File::put(resource_path('views/components/late.blade.php'), "@props(['a' => 1])\n\n<p>{{ \$a }}</p>\n{{-- Not the description. @group Elements --}}");

    expect(fn () => Catalog::custom())->toThrow(LogicException::class, 'late.blade.php names no group');
});

test('without component files of its own an imprint has no custom components', function () {
    File::deleteDirectory(resource_path('views/components'));
    File::deleteDirectory(resource_path('views/foundry'));

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

test('an example renders alone on a page of its own, for the frame at a viewport\'s width', function () {
    File::put(resource_path('views/foundry/head.blade.php'), '<title>{{ $title }}</title>');

    $this->get('/components/stamp/examples/0')->assertOk()->assertSee('<span>the stamp', false);
    $this->get('/components/stamp/examples/9')->assertNotFound();
    $this->get('/components/nothing/examples/0')->assertNotFound();
});

test('a Livewire component, single-file or multi-file, is no catalogue component', function () {
    File::put(resource_path('views/components/counter.blade.php'), "<?php\n\nnew class extends \\Livewire\\Component {};\n?>\n<div>0</div>");

    File::ensureDirectoryExists(resource_path('views/components/meter'));
    File::put(resource_path('views/components/meter/meter.blade.php'), '<div>0</div>');
    File::put(resource_path('views/components/meter/meter.php'), "<?php\n\nnew class extends \\Livewire\\Component {};");
    File::put(resource_path('views/components/meter/placeholder.blade.php'), '<div></div>');

    expect(array_keys(Catalog::custom()))->toContain('stamp')->not->toContain('counter', 'meter', 'meter-placeholder');
});

test('the component pages print docblock prose without its backticks, which the catalogue keeps for the skill', function () {
    File::put(resource_path('views/components/pin.blade.php'), <<<'BLADE'
@props(['size' => 'small'])
{{--
    Set `absolute` against an edge.
    @group Elements
    @prop size The step, `small` or `copy`.
    @slot trailing After the `line`.
    @example Alone
    <x-pin />
--}}
<span>the pin</span>
BLADE);

    expect(Catalog::custom()['pin']['description'])->toBe('Set `absolute` against an edge.');

    $this->get('/components/pin')->assertOk()
        ->assertSee('Set absolute against an edge.')
        ->assertSee('The step, small or copy.')
        ->assertSee('After the line.');

    $this->get('/components')->assertOk()->assertSee('Set absolute against an edge.');
});
