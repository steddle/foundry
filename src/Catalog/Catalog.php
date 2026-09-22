<?php

namespace Steddle\Foundry\Catalog;

use Composer\InstalledVersions;
use Illuminate\Support\Str;
use LogicException;
use Symfony\Component\Finder\Finder;

/**
 * Every component /components shows. A component describes itself in the
 * comment its file opens on: the prose is its description, `@group` its place
 * in the index, `@prop name …` and `@slot name …` a line each of its props
 * and slots, and each `@example Title` a live example, its Blade the lines
 * after it, optionally led by `@ground page|ink|bare`, where `bare` frames it
 * at a viewport's width, and `@code` to print the Blade without rendering it.
 * The foundry's components and the imprint's own are read the same way; the
 * lockup and the mark, which every imprint draws itself, are described here.
 */
final class Catalog
{
    /** The index's groups, in order. Every component names one, the imprint's as the foundry's. */
    private const GROUPS = ['Shell', 'Sections', 'Layout', 'Navigation', 'Type', 'Elements', 'Forms', 'Brand', 'Print'];

    /**
     * @return array<string, array{name: string, group: string, from: string, tag?: string, description: string, props: list<array{name: string, default: ?string, description: string}>, slots: list<array{name: string, description: string}>, examples: list<array{title: string, blade: string, ground?: string, code?: bool}>}>
     */
    public static function all(): array
    {
        return [...self::shared(), ...self::custom()];
    }

    /**
     * The foundry's own components, and the two every imprint supplies.
     *
     * @return array<string, array{name: string, group: string, from: string, tag?: string, description: string, props: list<array{name: string, default: ?string, description: string}>, slots: list<array{name: string, description: string}>, examples: list<array{title: string, blade: string, ground?: string, code?: bool}>}>
     */
    public static function shared(): array
    {
        return once(fn (): array => self::sorted([...self::read(__DIR__.'/../../resources/views/foundry', 'foundry'), ...self::supplied()]));
    }

    /**
     * The imprint's own components: every file under its
     * resources/views/components, each naming its group.
     *
     * @return array<string, array{name: string, group: string, from: string, tag?: string, description: string, props: list<array{name: string, default: ?string, description: string}>, slots: list<array{name: string, description: string}>, examples: list<array{title: string, blade: string, ground?: string, code?: bool}>}>
     */
    public static function custom(): array
    {
        return once(function (): array {
            // A test's dataset asks for the entries before the application boots, when Composer still knows the project's root.
            $root = (app()->bound('path.resources') ? resource_path() : realpath(InstalledVersions::getRootPackage()['install_path']).'/resources').'/views/components';

            $entries = self::read($root, 'custom');

            return self::sorted(array_map(fn (array $entry): array => $entry['examples'] === [] ? [...$entry, 'examples' => [['title' => 'Its tag', 'code' => true, 'blade' => "<{$entry['tag']} />"]]] : $entry, $entries));
        });
    }

    /**
     * Whether the imprint keeps a file of its own in place of the foundry's
     * component, which /components exists to show. The lockup and the mark it
     * supplies are no such stand-in.
     */
    public static function held(string $tag): bool
    {
        $name = str_replace('.', '/', Str::after($tag, 'foundry:'));

        if (in_array($name, ['lockup', 'mark'], true)) {
            return false;
        }

        return is_file(resource_path("views/foundry/{$name}.blade.php"))
            || is_file(resource_path("views/foundry/{$name}/index.blade.php"));
    }

    /**
     * @param  array<string, array{group: string, name: string}>  $entries
     * @return array<string, array{group: string, name: string}> in index order: by group, then by name
     */
    private static function sorted(array $entries): array
    {
        return collect($entries)->sortBy(fn (array $entry): string => sprintf('%02d', array_search($entry['group'], self::GROUPS)).$entry['name'])->all();
    }

    /**
     * @param  array<string, array{group: string}>|null  $entries  every entry where null
     * @return array<string, list<string>> group => slugs, in index order
     */
    public static function groups(?array $entries = null): array
    {
        return collect($entries ?? self::all())->map(fn (array $entry, string $slug): array => [$entry['group'], $slug])
            ->groupBy(0)
            ->map(fn ($pairs) => $pairs->pluck(1)->all())
            ->all();
    }

    /**
     * The components under a directory, the Livewire ones left out. A folder
     * with an index is one component and its other files are its parts; a
     * file in a folder named after a group is in that group, and every other
     * names its own, or the read throws.
     *
     * @return array<string, array{name: string, group: string, from: string, tag: string, file: string, description: string, props: list<array{name: string, default: ?string, description: string}>, slots: list<array{name: string, description: string}>, examples: list<array<string, mixed>>}>
     */
    private static function read(string $root, string $from): array
    {
        if (! is_dir($root)) {
            return [];
        }

        $entries = [];

        foreach (Finder::create()->files()->in($root)->name('*.blade.php')->sortByName() as $file) {
            if (self::livewire($file->getPathname(), $file->getContents())) {
                continue;
            }

            $relative = Str::before(str_replace(DIRECTORY_SEPARATOR, '/', $file->getRelativePathname()), '.blade.php');
            $path = Str::replaceLast('/index', '', $relative);

            if (! str_ends_with($relative, '/index') && str_contains($relative, '/') && is_file($root.'/'.Str::beforeLast($relative, '/').'/index.blade.php')) {
                continue;
            }

            $slug = str_replace(['/', '.'], '-', $path);

            $source = $file->getContents();
            ['description' => $description, 'group' => $group, 'examples' => $examples, 'props' => $documented, 'slots' => $slots] = self::comment($source);
            $group ??= str_contains($path, '/') ? Str::ucfirst(str_replace('-', ' ', Str::before($path, '/'))) : null;

            if (! in_array($group, self::GROUPS, true)) {
                throw new LogicException("{$relative}.blade.php names no group of the index. Give its opening comment `@group` and one of: ".implode(', ', self::GROUPS).'.');
            }

            $entries[$slug] = [
                'name' => Str::ucfirst(str_replace('-', ' ', Str::afterLast($path, '/'))),
                'group' => $group,
                'from' => $from,
                'tag' => ($from === 'foundry' ? 'foundry:' : 'x-').str_replace('/', '.', $path),
                'file' => $relative.'.blade.php',
                'description' => $description,
                'props' => self::props($source, $documented),
                'slots' => $slots,
                'examples' => $examples,
            ];
        }

        return $entries;
    }

    /**
     * Whether the file is Livewire's rather than Blade's: a single-file
     * component, which declares its class itself, or anything inside a
     * multi-file component's folder, which holds the class beside the view.
     */
    private static function livewire(string $path, string $source): bool
    {
        $directory = dirname($path);

        return is_file($directory.'/'.basename($directory).'.php')
            || is_file($directory.'/index.php')
            || is_file(Str::replaceLast('.blade.php', '.php', $path))
            || (bool) preg_match('/^\s*(?:<\?php|@php).*?\bnew class\b.*?\bextends\b/s', $source);
    }

    /**
     * What a component's opening comment says of it.
     *
     * @return array{description: string, group: ?string, examples: list<array{title: string, blade: string, ground?: string, code?: bool}>, props: array<string, string>, slots: list<array{name: string, description: string}>}
     */
    private static function comment(string $source): array
    {
        // Balanced parentheses, so a directive never reads on into the component's body.
        if (! preg_match('/^\s*(?:@(?:props|use)(\((?:[^()]++|(?1))*\))[ \t]*\n\s*)*\{\{--(.*?)--\}\}/s', $source, $match)) {
            return ['description' => '', 'group' => null, 'examples' => [], 'props' => [], 'slots' => []];
        }

        $parts = preg_split('/^\s*@example\s+(.+)$/m', $match[2], -1, PREG_SPLIT_DELIM_CAPTURE);
        $intro = array_shift($parts);
        $group = preg_match('/^\s*@group\s+(.+)$/m', $intro, $found) ? trim($found[1]) : null;
        preg_match_all('/^\s*@(prop|slot)\s+(\S+)\s+(.+)$/m', $intro, $tags, PREG_SET_ORDER);
        $examples = [];

        foreach (array_chunk($parts, 2) as [$title, $body]) {
            $example = ['title' => trim($title)];
            $lines = array_map(fn (string $line): string => preg_replace('/^ {4}/', '', $line), explode("\n", trim($body, "\n")));

            while ($lines !== [] && preg_match('/^@(ground|code)\b\s*(.*)$/', trim($lines[0]), $attribute)) {
                array_shift($lines);
                $example[$attribute[1]] = $attribute[1] === 'code' ? true : trim($attribute[2]);
            }

            $examples[] = [...$example, 'blade' => trim(implode("\n", $lines))];
        }

        return [
            'description' => trim(preg_replace('/\s+/', ' ', preg_replace('/^\s*@(group|prop|slot)\s+.+$/m', '', $intro))),
            'group' => $group,
            'examples' => $examples,
            'props' => collect($tags)->where(1, 'prop')->mapWithKeys(fn (array $tag): array => [$tag[2] => trim($tag[3])])->all(),
            'slots' => collect($tags)->where(1, 'slot')->map(fn (array $tag): array => ['name' => $tag[2], 'description' => trim($tag[3])])->values()->all(),
        ];
    }

    /**
     * Every prop the component's `@props` declares, with its default as PHP
     * writes it or null where it is required, and the `@prop` line that
     * describes it; a `@prop` for a name `@props` does not declare, an
     * attribute the component passes on, follows them as `passed on`.
     *
     * @param  array<string, string>  $documented  name => description
     * @return list<array{name: string, default: ?string, description: string}>
     */
    private static function props(string $source, array $documented): array
    {
        $declared = [];

        if (preg_match('/^\s*@props(\((?:[^()]++|(?1))*\))/m', $source, $match)) {
            try {
                // The lab runs outside production only, on the foundry's and the imprint's own files.
                $declared = eval('return '.$match[1].';');
            } catch (\Throwable) {
                $declared = [];
            }
        }

        $props = [];

        foreach ($declared as $key => $value) {
            $name = is_int($key) ? $value : $key;
            $props[$name] = [
                'name' => $name,
                'default' => match (true) {
                    is_int($key) => null,
                    is_array($value) => $value === [] ? '[]' : '[…]',
                    is_string($value) => var_export($value, true),
                    default => strtolower(var_export($value, true)),
                },
                'description' => $documented[$name] ?? '',
            ];
        }

        foreach (array_diff_key($documented, $props) as $name => $description) {
            $props[$name] = ['name' => $name, 'default' => 'passed on', 'description' => $description];
        }

        return array_values($props);
    }

    /**
     * @return array<string, array{name: string, group: string, from: string, description: string, props: list<mixed>, slots: list<mixed>, examples: list<array{title: string, blade: string, ground?: string}>}>
     */
    private static function supplied(): array
    {
        return [
            'lockup' => [
                'name' => 'Lockup',
                'group' => 'Brand',
                'from' => 'imprint',
                'props' => [],
                'slots' => [],
                'description' => 'The mark and the wordmark in the current colour. An imprint endorses it with BY STEDDLE where it stands without the house around it.',
                'examples' => [
                    ['title' => 'On the page', 'blade' => <<<'BLADE'
<foundry:lockup class="h-8 text-zinc-950 dark:text-zinc-50" />
BLADE],
                    ['title' => 'On ink', 'ground' => 'ink', 'blade' => <<<'BLADE'
<foundry:lockup class="h-8 text-zinc-950 dark:text-zinc-50" />
BLADE],
                ],
            ],
            'mark' => [
                'name' => 'Mark',
                'group' => 'Brand',
                'from' => 'imprint',
                'props' => [],
                'slots' => [],
                'description' => 'The imprint\'s mark on a 32 by 32 grid, in the current colour. The icons are drawn from it.',
                'examples' => [
                    ['title' => 'At three sizes', 'blade' => <<<'BLADE'
<div class="flex items-end gap-6 text-zinc-950 dark:text-zinc-50">
    <foundry:mark class="size-16" />
    <foundry:mark class="size-8" />
    <foundry:mark class="size-4" />
</div>
BLADE],
                ],
            ],
        ];
    }
}
