<?php

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Once;
use Steddle\Foundry\Boost\Skill;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

/**
 * As Boost renders it at boost:install: backticks, component tags and
 * `@props` held out of Blade behind placeholders, and entities decoded.
 */
function renderSkill(string $file): string
{
    $placeholders = ['`' => '___SINGLE_BACKTICK___', '@props' => '___PROPS_DIRECTIVE___', '</x-' => '___BLADE_COMPONENT_CLOSE___', '<x-' => '___BLADE_COMPONENT_OPEN___'];
    $source = str_replace(array_keys($placeholders), array_values($placeholders), file_get_contents(__DIR__.'/../resources/boost/skills/'.$file));

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
    $skill = renderSkill('foundry/SKILL.blade.php');

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
    $brand = renderSkill('foundry/references/components/brand.blade.php');

    expect($brand)
        ->toContain('## `x-stamp`')
        ->toContain('in `resources/views/components/stamp.blade.php`')
        ->toContain('| `ink` | `\'red\'` | The colour it is pressed in. |')
        ->toContain("```blade\n<x-stamp />\n```")
        ->not->toContain('## `foundry:heading`');

    expect(renderSkill('foundry/references/components/type.blade.php'))
        ->toContain('keeps a file of its own in place of the foundry\'s, in `resources/views/foundry/`');
});

test('every reference renders', function (string $file) {
    expect(renderSkill($file))->not->toBeEmpty()->not->toContain('@php')->not->toContain('@foreach')->not->toContain('@endif');
})->with(fn () => collect(Finder::create()->files()->in(__DIR__.'/../resources/boost/skills/foundry'))->map(fn ($file) => 'foundry/'.$file->getRelativePathname())->values()->all());

test('the fingerprint moves with the imprint\'s components, and an installed copy is read by it', function () {
    $before = Skill::fingerprint();
    File::ensureDirectoryExists(base_path('.agents/skills/foundry'));
    File::put(base_path('.agents/skills/foundry/SKILL.md'), renderSkill('foundry/SKILL.blade.php'));

    expect(Skill::installed())->toBe(['.agents/skills/foundry/SKILL.md' => $before]);

    File::put(resource_path('views/components/seal.blade.php'), "{{-- @group Elements --}}\n<span>the seal</span>");
    Once::flush();

    expect(Skill::fingerprint())->not->toBe($before);
});

test('every skill the foundry ships opens on the frontmatter Boost reads, and the guideline names it', function (string $skill) {
    $file = __DIR__.'/../resources/boost/skills/'.$skill.'/SKILL';
    $content = file_exists($file.'.blade.php') ? renderSkill($skill.'/SKILL.blade.php') : file_get_contents($file.'.md');

    expect(preg_match('/^\s*---[^\S\r\n]*\R(.*?)\R---[^\S\r\n]*(?:\R|$)/s', $content, $match))->toBe(1);

    $frontmatter = Yaml::parse($match[1]);

    expect($frontmatter['name'])->toBe($skill)
        ->and($frontmatter['description'])->toBeString()->not->toBeEmpty()
        ->and(file_get_contents(__DIR__.'/../resources/boost/guidelines/core.blade.php'))->toContain("`{$skill}`");
})->with(['foundry', 'laravel-code-simplifier', 'sync-docs']);

test('sync-docs reads the imprint\'s half from .ai/sync-docs.md', function () {
    expect(file_get_contents(__DIR__.'/../resources/boost/skills/sync-docs/SKILL.md'))
        ->toContain('`.ai/sync-docs.md`')
        ->toContain('**Surfaces**', '**Areas**', '**Ground truth**', '**Rounds**', '**Checks**');
});

test('the foundry\'s own sessions read the simplifier the imprints receive', function () {
    expect(realpath(__DIR__.'/../.claude/skills/laravel-code-simplifier'))
        ->toBe(realpath(__DIR__.'/../resources/boost/skills/laravel-code-simplifier'));
});
