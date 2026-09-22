<?php

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Once;
use Steddle\Foundry\Boost\Skill;
use Symfony\Component\Finder\Finder;

/**
 * A skill file as Boost renders it at boost:install, in the imprint's
 * application: backticks, component tags and `@props` held out of Blade
 * behind placeholders, and entities decoded.
 */
function renderSkill(string $file): string
{
    $placeholders = ['`' => '___SINGLE_BACKTICK___', '@props' => '___PROPS_DIRECTIVE___', '</x-' => '___BLADE_COMPONENT_CLOSE___', '<x-' => '___BLADE_COMPONENT_OPEN___'];
    $source = str_replace(array_keys($placeholders), array_values($placeholders), file_get_contents(__DIR__.'/../resources/boost/skills/foundry/'.$file));

    return str_replace(array_values($placeholders), array_keys($placeholders), html_entity_decode(Blade::render($source), ENT_QUOTES | ENT_HTML5));
}

beforeEach(function () {
    Once::flush();
    config(['imprint.name' => 'Stagent']);
    File::ensureDirectoryExists(resource_path('views/foundry'));
    File::ensureDirectoryExists(resource_path('views/components'));
    File::put(resource_path('views/foundry/heading.blade.php'), '<h2 {{ $attributes }}>{{ $slot }}</h2>');
    File::put(resource_path('views/components/stamp.blade.php'), <<<'BLADE'
@props(['ink' => 'red'])
{{--
    The imprint's own stamp. Pressed in red.
    @group Brand
    @prop ink The colour it is pressed in.
    @example Alone
    <x-stamp />
--}}
<span>the stamp</span>
BLADE);
});

afterEach(function () {
    File::deleteDirectory(resource_path('views/components'));
    File::deleteDirectory(resource_path('views/foundry'));
    File::deleteDirectory(base_path('.agents'));
    Once::flush();
});

test('the skill indexes every component the imprint renders, marking its own and the foundry\'s it copies', function () {
    $skill = renderSkill('SKILL.blade.php');

    expect($skill)
        ->toStartWith("---\nname: foundry\n")
        ->toContain('# Foundry in Stagent')
        ->toContain("- `x-stamp` (own): The imprint's own stamp.\n")
        ->toContain('- `foundry:heading` (✗ stood in for)')
        ->toContain('- `foundry:sections.hero`: ')
        ->toContain('`@props`', '`@slot name …`')
        ->toContain('<!-- foundry-skill '.Skill::fingerprint().' -->');
});

test('a group\'s reference holds each component\'s props, slots and example', function () {
    $brand = renderSkill('references/components/brand.blade.php');

    expect($brand)
        ->toContain('## `x-stamp`')
        ->toContain('in `resources/views/components/stamp.blade.php`')
        ->toContain('| `ink` | `\'red\'` | The colour it is pressed in. |')
        ->toContain("```blade\n<x-stamp />\n```")
        ->not->toContain('## `foundry:heading`');

    expect(renderSkill('references/components/type.blade.php'))
        ->toContain('keeps a file of its own in place of the foundry\'s, in `resources/views/foundry/`');
});

test('every reference renders', function (string $file) {
    expect(renderSkill($file))->not->toBeEmpty()->not->toContain('@php')->not->toContain('@foreach')->not->toContain('@endif');
})->with(fn () => collect(Finder::create()->files()->in(__DIR__.'/../resources/boost/skills/foundry'))->map(fn ($file) => $file->getRelativePathname())->values()->all());

test('the fingerprint moves with the imprint\'s components, and an installed copy is read by it', function () {
    $before = Skill::fingerprint();
    File::ensureDirectoryExists(base_path('.agents/skills/foundry'));
    File::put(base_path('.agents/skills/foundry/SKILL.md'), renderSkill('SKILL.blade.php'));

    expect(Skill::installed())->toBe(['.agents/skills/foundry/SKILL.md' => $before]);

    File::put(resource_path('views/components/seal.blade.php'), "{{-- @group Elements --}}\n<span>the seal</span>");
    Once::flush();

    expect(Skill::fingerprint())->not->toBe($before);
});
