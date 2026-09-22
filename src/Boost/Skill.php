<?php

namespace Steddle\Foundry\Boost;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Steddle\Foundry\Catalog\Catalog;
use Steddle\Foundry\Content;
use Steddle\Foundry\Locales;

/**
 * What the foundry skill states of the imprint it is installed in. Boost
 * renders `resources/boost/skills/foundry` inside the imprint at
 * `boost:install` and `boost:update`, so the installed skill holds these facts
 * as they stood then; `fingerprint()` closes its SKILL.md, and the imprint's
 * suite fails once the fingerprint no longer matches.
 */
final class Skill
{
    /**
     * @return array{name: string, locales: list<string>, sitemap: ?string, scenes: list<string>, labs: array<string, string>, og: array<string, string>, banner: array<string, string>, docs: array<string, list<string>>|null, legal: array<string, list<string>>|null, components: array<string, array{tag: string, name: string, group: string, from: string, file: ?string, held: ?string, description: string, props: list<array{name: string, default: ?string, description: string}>, slots: list<array{name: string, description: string}>, example: ?string}>}
     */
    public static function facts(): array
    {
        return once(fn (): array => [
            'name' => config('imprint.name'),
            'locales' => Locales::all(),
            'sitemap' => config('imprint.sitemap'),
            'scenes' => array_keys(config('imprint.scenes', [])),
            'labs' => collect(config('imprint.labs', []))->map(fn (array $lab): string => $lab[0])->all(),
            'og' => config('imprint.og', []),
            'banner' => config('imprint.banner', []),
            'docs' => self::docs(),
            'legal' => self::legal(),
            'components' => collect(Catalog::all())->map(fn (array $entry, string $slug): array => [
                'tag' => $entry['tag'] ?? 'foundry:'.$slug,
                'name' => $entry['name'],
                'group' => $entry['group'],
                'from' => $entry['from'],
                'file' => $entry['from'] === 'custom' ? 'resources/views/components/'.$entry['file'] : null,
                'held' => $entry['from'] === 'foundry' && Catalog::held($entry['tag']),
                'description' => $entry['description'],
                'props' => $entry['props'],
                'slots' => $entry['slots'],
                'example' => self::example($entry['examples']),
            ])->all(),
        ]);
    }

    /** The reference to one group's components, in markdown. */
    public static function components(string $group): string
    {
        $facts = self::facts();

        return view('foundry::skill.components', [
            'group' => $group,
            'name' => $facts['name'],
            'components' => array_filter($facts['components'], fn (array $component): bool => $component['group'] === $group),
        ])->render();
    }

    /**
     * Values as inline code, for a line of prose.
     *
     * @param  iterable<string>  $values
     */
    public static function codes(iterable $values): string
    {
        return collect($values)->map(fn (string $value): string => "`{$value}`")->implode(', ');
    }

    /** The first sentence of a description, for the index. */
    public static function summary(string $description): string
    {
        return preg_match('/^.+?[.!?](?=\s|$)/s', $description, $match) ? $match[0] : $description;
    }

    public static function fingerprint(): string
    {
        return substr(sha1(json_encode(self::facts())), 0, 12);
    }

    /**
     * The fingerprint an installed copy of the skill closes on, keyed by its
     * path: every agent's skills directory Boost writes to.
     *
     * @return array<string, ?string>
     */
    public static function installed(): array
    {
        $installed = [];

        foreach (glob(base_path('{.*,.github}/skills/foundry/SKILL.md'), GLOB_BRACE) ?: [] as $path) {
            $installed[Str::after($path, base_path().'/')] = preg_match('/<!-- foundry-skill (\w+) -->/', file_get_contents($path), $match) ? $match[1] : null;
        }

        return $installed;
    }

    /**
     * The first example that renders in the page's flow, else the first.
     *
     * @param  list<array{blade: string, ground?: string}>  $examples
     */
    private static function example(array $examples): ?string
    {
        foreach ($examples as $example) {
            if (($example['ground'] ?? null) !== 'bare') {
                return $example['blade'];
            }
        }

        return $examples[0]['blade'] ?? null;
    }

    /** @return array<string, list<string>>|null topic => its published articles, in the root language; null where docs are not routed */
    private static function docs(): ?array
    {
        if (! self::routed('docs')) {
            return null;
        }

        return collect(Content::docs(Locales::root()))->map(fn (array $topic): array => array_keys($topic['articles']))->all();
    }

    /** @return array<string, list<string>>|null audience => its published documents, in the root language; null where legal is not routed */
    private static function legal(): ?array
    {
        if (! self::routed('legal')) {
            return null;
        }

        return collect(Content::legal(Locales::root())['audiences'])->map(fn (array $audience): array => array_keys($audience['documents']))->all();
    }

    private static function routed(string $section): bool
    {
        return Route::has("{$section}.index") || Route::has(Locales::root().".{$section}.index");
    }
}
