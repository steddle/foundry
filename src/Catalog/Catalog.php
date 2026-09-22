<?php

namespace Steddle\Foundry\Catalog;

use Composer\InstalledVersions;
use Illuminate\Support\Str;
use LogicException;
use Symfony\Component\Finder\Finder;

/**
 * Every component /components shows. A component describes itself in the
 * comment its file opens on: the prose is its description, `@group` its place
 * in the index, and each `@example Title` a live example, its Blade the lines
 * after it, optionally led by `@ground page|ink|bare`, `@zoom 0.5` to shrink a
 * fixed-size render to the column, and `@code` to print the Blade without
 * rendering it. The foundry's components and the imprint's own are read the
 * same way; the lockup and the mark, which every imprint draws itself, are
 * described here.
 */
final class Catalog
{
    /** The index's groups, in order. An imprint's own component without a group is `Custom`. */
    private const GROUPS = ['Layout', 'Type', 'Actions', 'Forms', 'Brand', 'Bands', 'Sections', 'Images'];

    /**
     * @return array<string, array{name: string, group: string, from: string, tag?: string, description: string, examples: list<array{title: string, blade: string, ground?: string, zoom?: float, code?: bool}>}>
     */
    public static function all(): array
    {
        return [...self::shared(), ...self::custom()];
    }

    /**
     * The foundry's own components, and the two every imprint supplies.
     *
     * @return array<string, array{name: string, group: string, from: string, tag?: string, description: string, examples: list<array{title: string, blade: string, ground?: string, zoom?: float, code?: bool}>}>
     */
    public static function shared(): array
    {
        return once(fn (): array => collect([...self::read(__DIR__.'/../../resources/views/components/site', 'foundry'), ...self::supplied()])
            ->sortBy(fn (array $entry, string $slug): string => sprintf('%02d', array_search($entry['group'], self::GROUPS)).$slug)
            ->all());
    }

    /**
     * The imprint's own components: every file under its
     * resources/views/components/site that the foundry neither keeps nor names.
     *
     * @return array<string, array{name: string, group: string, from: string, tag: string, description: string, examples: list<array{title: string, blade: string, ground?: string, zoom?: float, code?: bool}>}>
     */
    public static function custom(): array
    {
        // A test's dataset asks for the entries before the application boots, when Composer still knows the project's root.
        $root = (app()->bound('path.resources') ? resource_path() : realpath(InstalledVersions::getRootPackage()['install_path']).'/resources').'/views/components/site';
        $shared = self::shared();

        $entries = collect(self::read($root, 'custom'))
            ->reject(fn (array $entry, string $slug): bool => isset($shared[$slug]) || is_file(__DIR__.'/../../resources/views/components/site/'.$entry['file']))
            ->map(fn (array $entry): array => $entry['examples'] === [] ? [...$entry, 'examples' => [['title' => 'Its tag', 'code' => true, 'blade' => "<{$entry['tag']} />"]]] : $entry);

        return $entries->sortBy(fn (array $entry): string => ($entry['group'] === 'Custom' ? '0' : '1').$entry['group'].$entry['name'])->all();
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
     * The components under a directory. A folder with an index is one
     * component and its other files are its parts; a folder without one is a
     * group of its own.
     *
     * @return array<string, array{name: string, group: string, from: string, tag: string, file: string, description: string, examples: list<array<string, mixed>>}>
     */
    private static function read(string $root, string $from): array
    {
        if (! is_dir($root)) {
            return [];
        }

        $entries = [];

        foreach (Finder::create()->files()->in($root)->name('*.blade.php')->sortByName() as $file) {
            $relative = Str::before(str_replace(DIRECTORY_SEPARATOR, '/', $file->getRelativePathname()), '.blade.php');
            $path = Str::replaceLast('/index', '', $relative);

            if (! str_ends_with($relative, '/index') && str_contains($relative, '/') && is_file($root.'/'.Str::beforeLast($relative, '/').'/index.blade.php')) {
                continue;
            }

            ['description' => $description, 'group' => $group, 'examples' => $examples] = self::comment($file->getContents());

            // By the foundry's own convention every component names its group, so one that does not failed to parse.
            if ($from === 'foundry' && ! in_array($group, self::GROUPS, true)) {
                throw new LogicException("The foundry's {$relative} names no group of the index, or one it does not have.");
            }

            $entries[str_replace(['/', '.'], '-', $path)] = [
                'name' => Str::ucfirst(str_replace(['-', '/'], [' ', ': '], $path)),
                'group' => $group ?? (str_contains($path, '/') ? Str::ucfirst(str_replace('-', ' ', Str::before($path, '/'))) : 'Custom'),
                'from' => $from,
                'tag' => 'x-site.'.str_replace('/', '.', $path),
                'file' => $relative.'.blade.php',
                'description' => $description,
                'examples' => $examples,
            ];
        }

        return $entries;
    }

    /**
     * What a component's opening comment says of it.
     *
     * @return array{description: string, group: ?string, examples: list<array{title: string, blade: string, ground?: string, zoom?: float, code?: bool}>}
     */
    private static function comment(string $source): array
    {
        // Balanced parentheses, so a directive never reads on into the component's body.
        if (! preg_match('/^\s*(?:@(?:props|use)(\((?:[^()]++|(?1))*\))[ \t]*\n\s*)*\{\{--(.*?)--\}\}/s', $source, $match)) {
            return ['description' => '', 'group' => null, 'examples' => []];
        }

        $parts = preg_split('/^\s*@example\s+(.+)$/m', $match[2], -1, PREG_SPLIT_DELIM_CAPTURE);
        $intro = array_shift($parts);
        $group = preg_match('/^\s*@group\s+(.+)$/m', $intro, $found) ? trim($found[1]) : null;
        $examples = [];

        foreach (array_chunk($parts, 2) as [$title, $body]) {
            $example = ['title' => trim($title)];
            $lines = array_map(fn (string $line): string => preg_replace('/^ {4}/', '', $line), explode("\n", trim($body, "\n")));

            while ($lines !== [] && preg_match('/^@(ground|zoom|code)\b\s*(.*)$/', trim($lines[0]), $attribute)) {
                array_shift($lines);
                $example[$attribute[1]] = match ($attribute[1]) {
                    'zoom' => (float) $attribute[2],
                    'code' => true,
                    default => trim($attribute[2]),
                };
            }

            $examples[] = [...$example, 'blade' => trim(implode("\n", $lines))];
        }

        return [
            'description' => trim(preg_replace('/\s+/', ' ', preg_replace('/^\s*@group\s+.+$/m', '', $intro))),
            'group' => $group,
            'examples' => $examples,
        ];
    }

    /**
     * @return array<string, array{name: string, group: string, from: string, description: string, examples: list<array{title: string, blade: string, ground?: string}>}>
     */
    private static function supplied(): array
    {
        return [
            'lockup' => [
                'name' => 'Lockup',
                'group' => 'Brand',
                'from' => 'imprint',
                'description' => 'The mark and the wordmark in the current colour. An imprint endorses it with BY STEDDLE where it stands without the house around it.',
                'examples' => [
                    ['title' => 'On the page', 'blade' => <<<'BLADE'
<x-site.lockup class="h-8 text-zinc-950 dark:text-zinc-50" />
BLADE],
                    ['title' => 'On ink', 'ground' => 'ink', 'blade' => <<<'BLADE'
<x-site.lockup class="h-8 text-zinc-950 dark:text-zinc-50" />
BLADE],
                ],
            ],
            'mark' => [
                'name' => 'Mark',
                'group' => 'Brand',
                'from' => 'imprint',
                'description' => 'The imprint\'s mark on a 32 by 32 grid, in the current colour. The icons are drawn from it.',
                'examples' => [
                    ['title' => 'At three sizes', 'blade' => <<<'BLADE'
<div class="flex items-end gap-6 text-zinc-950 dark:text-zinc-50">
    <x-site.mark class="size-16" />
    <x-site.mark class="size-8" />
    <x-site.mark class="size-4" />
</div>
BLADE],
                ],
            ],
        ];
    }
}
