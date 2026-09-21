<?php

namespace Steddle\Foundry\Catalog;

use Composer\InstalledVersions;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;

/**
 * Every component /components shows, in the order of its index. An example is
 * Blade the page renders live and prints beside it, so an imprint sees what it
 * renders itself, its own version of a component included. `ground` is the
 * band an example stands on, `page` or `ink`; `zoom` shrinks a fixed-size
 * render to the column; `code` alone prints the Blade without rendering it.
 * An imprint's own components join them, read from its own component files.
 */
final class Catalog
{
    /**
     * @return array<string, array{name: string, group: string, from: string, description: string, examples: list<array{title: string, blade: string, ground?: string, zoom?: float, code?: bool}>}>
     */
    public static function all(): array
    {
        return [...self::shared(), ...self::custom()];
    }

    /**
     * The imprint's own components: every file under its
     * resources/views/components/site that neither the foundry keeps nor its
     * catalogue names. The comment a file opens on is its description, and
     * each `@example Title` line in it starts a live example, its Blade the
     * lines after it. A component with none shows its tag alone.
     *
     * @return array<string, array{name: string, group: string, from: string, tag: string, description: string, examples: list<array{title: string, blade: string, code?: bool}>}>
     */
    public static function custom(): array
    {
        // A test's dataset asks for the entries before the application boots, when Composer still knows the project's root.
        $root = (app()->bound('path.resources') ? resource_path() : realpath(InstalledVersions::getRootPackage()['install_path']).'/resources').'/views/components/site';
        $foundry = __DIR__.'/../../resources/views/components/site';

        if (! is_dir($root)) {
            return [];
        }

        $entries = [];

        foreach (Finder::create()->files()->in($root)->name('*.blade.php')->sortByName() as $file) {
            $relative = Str::before(str_replace(DIRECTORY_SEPARATOR, '/', $file->getRelativePathname()), '.blade.php');
            $tag = Str::replaceLast('/index', '', $relative);
            $slug = str_replace(['/', '.'], '-', $tag);

            if (is_file("{$foundry}/{$relative}.blade.php") || isset(self::shared()[$slug])) {
                continue;
            }

            [$description, $examples] = self::read($file->getContents());
            $tag = 'x-site.'.str_replace('/', '.', $tag);

            $entries[$slug] = [
                'name' => Str::ucfirst(str_replace(['-', '/'], [' ', ': '], Str::replaceLast('/index', '', $relative))),
                'group' => str_contains($relative, '/') ? Str::ucfirst(str_replace('-', ' ', Str::before($relative, '/'))) : 'Custom',
                'from' => 'custom',
                'tag' => $tag,
                'description' => $description,
                'examples' => $examples ?: [['title' => 'Its tag', 'code' => true, 'blade' => "<{$tag} />"]],
            ];
        }

        return collect($entries)->sortBy(fn (array $entry): string => ($entry['group'] === 'Custom' ? '0' : '1').$entry['group'].$entry['name'])->all();
    }

    /**
     * The description and examples a component's opening comment carries.
     *
     * @return array{0: string, 1: list<array{title: string, blade: string}>}
     */
    private static function read(string $source): array
    {
        // The comment may follow the component's @props and @use lines.
        if (! preg_match('/^\s*(?:@(?:props|use)\(.*?\)[ \t]*\n\s*)*\{\{--(.*?)--\}\}/s', $source, $match)) {
            return ['', []];
        }

        $parts = preg_split('/^\s*@example\s+(.+)$/m', $match[1], -1, PREG_SPLIT_DELIM_CAPTURE);
        $description = trim(preg_replace('/\s+/', ' ', array_shift($parts)));
        $examples = [];

        foreach (array_chunk($parts, 2) as [$title, $blade]) {
            $examples[] = ['title' => trim($title), 'blade' => trim(implode("\n", array_map(fn (string $line): string => preg_replace('/^ {4}/', '', $line), explode("\n", $blade))))];
        }

        return [$description, $examples];
    }

    /**
     * The foundry's own entries, the ones every imprint supplies included.
     *
     * @return array<string, array{name: string, group: string, from: string, description: string, examples: list<array{title: string, blade: string, ground?: string, zoom?: float, code?: bool}>}>
     */
    public static function shared(): array
    {
        return [
            'container' => [
                'name' => 'Container',
                'group' => 'Layout',
                'from' => 'foundry',
                'description' => 'The page\'s width and gutters. Every band sets its content in one.',
                'examples' => [
                    ['title' => 'Content at the page\'s width', 'blade' => <<<'BLADE'
<x-site.container>
    <div class="rounded-md border border-dashed border-zinc-200 dark:border-zinc-700 p-6 text-zinc-600 dark:text-zinc-400">Wide, with the phone and desktop gutters.</div>
</x-site.container>
BLADE],
                ],
            ],
            'section' => [
                'name' => 'Section',
                'group' => 'Layout',
                'from' => 'foundry',
                'description' => 'A band of the page, spaced from the next, with a hairline between bands in dark mode. With a scene it is ink over that photo, under the measured scrim.',
                'examples' => [
                    ['title' => 'A band with a heading and a lede', 'ground' => 'bare', 'blade' => <<<'BLADE'
<x-site.section>
    <x-site.heading size="2">A section heading.</x-site.heading>
    <x-site.text variant="lede">The lede under it, in the body colour.</x-site.text>
</x-site.section>
BLADE],
                ],
            ],
            'breadcrumb' => [
                'name' => 'Breadcrumb',
                'group' => 'Layout',
                'from' => 'foundry',
                'description' => 'The pages above this one, outermost first. The page\'s own title stands under it, so the trail names only what is above.',
                'examples' => [
                    ['title' => 'Two levels up', 'blade' => <<<'BLADE'
<x-site.breadcrumb :items="['Docs' => '#', 'Getting started' => '#']" />
BLADE],
                ],
            ],
            'numbered-section' => [
                'name' => 'Numbered section',
                'group' => 'Layout',
                'from' => 'foundry',
                'description' => 'A section that opens on a ruled row: its number, its name, one fact about what follows, and the actions on it.',
                'examples' => [
                    ['title' => 'With a note', 'blade' => <<<'BLADE'
<x-site.numbered-section number="04" name="Social images" note="4 files">
    <x-site.text>What follows the row.</x-site.text>
</x-site.numbered-section>
BLADE],
                ],
            ],
            'document' => [
                'name' => 'Document',
                'group' => 'Layout',
                'from' => 'foundry',
                'description' => 'A reading layout: navigation beside the text on wide screens, above it on narrow ones. The navigation is left out of a page\'s markdown.',
                'examples' => [
                    ['title' => 'Side navigation and text', 'blade' => <<<'BLADE'
<x-site.document>
    <x-slot:aside>
        <x-site.side-nav :groups="['On this page' => [['label' => 'Install', 'href' => '#', 'current' => true], ['label' => 'Configure', 'href' => '#']]]" />
    </x-slot:aside>
    <x-site.text>The text beside it.</x-site.text>
</x-site.document>
BLADE],
                ],
            ],
            'side-nav' => [
                'name' => 'Side navigation',
                'group' => 'Layout',
                'from' => 'foundry',
                'description' => 'Groups of links with the current page marked in the accent, folded behind a disclosure on narrow screens.',
                'examples' => [
                    ['title' => 'Two groups', 'blade' => <<<'BLADE'
<x-site.side-nav :groups="[
    'Getting started' => [['label' => 'Installation', 'href' => '#', 'current' => true], ['label' => 'Theming', 'href' => '#']],
    'Components' => [['label' => 'Button', 'href' => '#'], ['label' => 'Heading', 'href' => '#']],
]" />
BLADE],
                ],
            ],
            'link-row' => [
                'name' => 'Link row',
                'group' => 'Layout',
                'from' => 'foundry',
                'description' => 'One row in a ruled list: a serif title, a sentence, an arrow. The parent draws the rules.',
                'examples' => [
                    ['title' => 'Two rows', 'blade' => <<<'BLADE'
<div class="flex flex-col divide-y divide-zinc-200 border-y border-zinc-200 dark:divide-zinc-700 dark:border-zinc-700">
    <x-site.link-row href="#" title="Design">The brand, the colours, the type and the assets, on one page.</x-site.link-row>
    <x-site.link-row href="#" title="Components">Every component the site renders, live.</x-site.link-row>
</div>
BLADE],
                ],
            ],
            'tile-grid' => [
                'name' => 'Tiles',
                'group' => 'Layout',
                'from' => 'foundry',
                'description' => 'Tiles on a hairline grid, one to three columns, with blank tiles closing the last row. A topic tile names its count, its title and what it covers, and lists its first links.',
                'examples' => [
                    ['title' => 'Two topics', 'blade' => <<<'BLADE'
<x-site.tile-grid :count="2">
    <x-site.topic icon="command-line" eyebrow="6 articles" title="The CLI" href="#" :links="['bron law' => '#', 'bron case' => '#']" more="All 6 articles">
        Every command, every option, and what a script can rely on when it reads the output.
    </x-site.topic>
    <x-site.topic icon="book-open" eyebrow="2 articles" title="Concepts" href="#" :links="['Two dates' => '#', 'Two lanes' => '#']">
        The ideas the answers rest on.
    </x-site.topic>
</x-site.tile-grid>
BLADE],
                ],
            ],
            'heading' => [
                'name' => 'Heading',
                'group' => 'Type',
                'from' => 'imprint',
                'description' => 'Spectral, on the imprint\'s scale. Colour comes from the tone alone.',
                'examples' => [
                    ['title' => 'The four sizes', 'blade' => <<<'BLADE'
<div class="flex flex-col gap-6">
    <x-site.heading size="display">Display heading.</x-site.heading>
    <x-site.heading size="1">Heading one.</x-site.heading>
    <x-site.heading size="2">Heading two.</x-site.heading>
    <x-site.heading size="3">Heading three.</x-site.heading>
</div>
BLADE],
                ],
            ],
            'text' => [
                'name' => 'Text',
                'group' => 'Type',
                'from' => 'imprint',
                'description' => 'Chivo, in a variant for its role and a tone for its colour.',
                'examples' => [
                    ['title' => 'Variants', 'blade' => <<<'BLADE'
<div class="flex flex-col gap-4">
    <x-site.text variant="lede">A lede opens a section.</x-site.text>
    <x-site.text>Copy carries the text.</x-site.text>
    <x-site.text variant="small">Small sets a caption or a note.</x-site.text>
    <x-site.text variant="label" tone="muted">A label names what follows</x-site.text>
</div>
BLADE],
                ],
            ],
            'marker' => [
                'name' => 'Marker',
                'group' => 'Type',
                'from' => 'imprint',
                'description' => 'Lichen behind one phrase of the hero headline. One per viewport, never on body copy or anything clickable.',
                'examples' => [
                    ['title' => 'On a headline, on ink', 'ground' => 'ink', 'blade' => <<<'BLADE'
<x-site.heading size="1">A headline with <x-site.marker>one phrase marked.</x-site.marker></x-site.heading>
BLADE],
                ],
            ],
            'button' => [
                'name' => 'Button',
                'group' => 'Actions',
                'from' => 'foundry',
                'description' => 'One primary per viewport. Secondary on a card, ghost on ink.',
                'examples' => [
                    ['title' => 'Primary and secondary', 'blade' => <<<'BLADE'
<div class="flex flex-wrap gap-4">
    <x-site.button href="#">Primary</x-site.button>
    <x-site.button href="#" variant="secondary">Secondary</x-site.button>
</div>
BLADE],
                    ['title' => 'Ghost, on ink', 'ground' => 'ink', 'blade' => <<<'BLADE'
<x-site.button href="#" variant="ghost">Ghost</x-site.button>
BLADE],
                ],
            ],
            'badge' => [
                'name' => 'Badge',
                'group' => 'Actions',
                'from' => 'foundry',
                'description' => 'A short status beside what it describes: a status ramp\'s 50 as the ground and its 700 as the text. Action, in lichen, is the one tone that asks the reader to do something.',
                'examples' => [
                    ['title' => 'Every tone', 'blade' => <<<'BLADE'
<div class="flex flex-wrap items-center gap-2.5">
    <x-site.badge>Neutral</x-site.badge>
    <x-site.badge tone="info">Info</x-site.badge>
    <x-site.badge tone="success">Success</x-site.badge>
    <x-site.badge tone="warning">Warning</x-site.badge>
    <x-site.badge tone="danger">Danger</x-site.badge>
    <x-site.badge tone="action" dot>Action</x-site.badge>
</div>
BLADE],
                ],
            ],
            'actions' => [
                'name' => 'Actions',
                'group' => 'Actions',
                'from' => 'foundry',
                'description' => 'The row of buttons a band, a page or a form ends on. A page\'s markdown leaves it out.',
                'examples' => [
                    ['title' => 'Two buttons', 'blade' => <<<'BLADE'
<x-site.actions>
    <x-site.button href="#">Talk to us</x-site.button>
    <x-site.button href="#" variant="secondary">Read the docs</x-site.button>
</x-site.actions>
BLADE],
                ],
            ],
            'copy-menu' => [
                'name' => 'Copy menu',
                'group' => 'Actions',
                'from' => 'foundry',
                'description' => 'Copies or opens the page\'s own markdown, or every page\'s at once, with the token count of each. It fetches both on the first hover or focus.',
                'examples' => [
                    ['title' => 'Beside a page title', 'blade' => <<<'BLADE'
<x-site.copy-menu />
BLADE],
                ],
            ],
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
            'favicons' => [
                'name' => 'Favicons',
                'group' => 'Brand',
                'from' => 'foundry',
                'description' => 'The icons foundry:assets draws, linked in the head, with the browser chrome in the imprint\'s ink and paper.',
                'examples' => [
                    ['title' => 'In the head', 'code' => true, 'blade' => <<<'BLADE'
<x-site.favicons />
BLADE],
                ],
            ],
            'hero' => [
                'name' => 'Hero',
                'group' => 'Bands',
                'from' => 'foundry',
                'description' => 'The ink band a page opens on, under the header: an eyebrow, a title with one marked phrase, a lede, then the slot. It sits at the start or in the centre, and the scrim follows; tall takes the screen\'s height and a display title.',
                'examples' => [
                    ['title' => 'A page', 'ground' => 'bare', 'blade' => <<<'BLADE'
<x-site.hero title="A page title." lead="The lead that says what the page is for, in a sentence or two." />
BLADE],
                    ['title' => 'Centred and tall', 'ground' => 'bare', 'zoom' => 0.5, 'blade' => <<<'BLADE'
<x-site.hero align="center" tall eyebrow="The eyebrow" title="The first thing a reader sees." marked="a reader sees." lead="A lede under it, centred.">
    <x-site.button href="#">An action</x-site.button>
</x-site.hero>
BLADE],
                ],
            ],
            'scene' => [
                'name' => 'Scene',
                'group' => 'Bands',
                'from' => 'foundry',
                'description' => 'The photo an ink band carries behind its content, with the scrim that keeps the content legible on it.',
                'examples' => [
                    ['title' => 'Behind a band', 'blade' => <<<'BLADE'
<div class="relative isolate flex h-72 items-end overflow-hidden bg-zinc-900 ink p-8">
    <x-site.scene :name="array_key_first(config('imprint.scenes'))" scrim="bg-zinc-900/60" />
    <x-site.heading size="2">Content on the scene.</x-site.heading>
</div>
BLADE],
                ],
            ],
            'nav' => [
                'name' => 'Header',
                'group' => 'Bands',
                'from' => 'foundry',
                'description' => 'The bar every page lays over its first band: the lockup, the links and the actions, folded into a menu on a phone.',
                'examples' => [
                    ['title' => 'As this imprint sets it', 'ground' => 'bare', 'blade' => <<<'BLADE'
<div class="relative h-20 bg-zinc-900 ink">
    <x-site.nav />
</div>
BLADE],
                ],
            ],
            'closing' => [
                'name' => 'Closing',
                'group' => 'Bands',
                'from' => 'foundry',
                'description' => 'The page\'s last word, in the footer\'s slot on its scene: a title, a lede and the actions. Centred it stands alone; at the start it stacks on the dark side of the scrim, clear of the scene\'s subject.',
                'examples' => [
                    ['title' => 'Centred', 'ground' => 'ink', 'blade' => <<<'BLADE'
<x-site.closing title="The last thing a reader sees.">
    <x-site.button href="#">An action</x-site.button>
</x-site.closing>
BLADE],
                    ['title' => 'At the start, with a lede', 'ground' => 'ink', 'blade' => <<<'BLADE'
<x-site.closing align="start" title="Where to go when the docs did not answer." lead="One sentence on what the action does.">
    <x-site.button href="#">An action</x-site.button>
</x-site.closing>
BLADE],
                ],
            ],
            'page-title' => [
                'name' => 'Page title',
                'group' => 'Bands',
                'from' => 'foundry',
                'description' => 'The first band of a page with no hero, under the lab\'s bar: its title and lede, then what the page holds, in the same band.',
                'examples' => [
                    ['title' => 'A title and a lede', 'ground' => 'bare', 'blade' => <<<'BLADE'
<x-site.page-title title="Lab." lead="The design system, every component the site renders, and the questions still open." />
BLADE],
                ],
            ],
            'service-line' => [
                'name' => 'Service line',
                'group' => 'Bands',
                'from' => 'foundry',
                'description' => 'The line every page closes on: the disclaimer, the copyright, and the house that serves the imprint where it is endorsed. The footer and the ink pages both end on it.',
                'examples' => [
                    ['title' => 'On ink', 'ground' => 'ink', 'blade' => <<<'BLADE'
<x-site.service-line />
BLADE],
                ],
            ],
            'ink-page' => [
                'name' => 'Ink page',
                'group' => 'Bands',
                'from' => 'foundry',
                'description' => 'A page of its own on ink, for an error or for signing in: the lockup, the slot, and the service line, kept out of search. A scene lays a photo under it; a card sets the slot in the middle.',
                'examples' => [
                    ['title' => 'An error page', 'code' => true, 'blade' => <<<'BLADE'
<x-site.ink-page title="Page not found" description="Nothing answers at this address." scene="error">
    <x-site.text variant="label" tone="accent">Error 404</x-site.text>
    <x-site.heading size="1" level="1">Page not found</x-site.heading>
</x-site.ink-page>
BLADE],
                ],
            ],
            'footer' => [
                'name' => 'Footer',
                'group' => 'Bands',
                'from' => 'foundry',
                'description' => 'Ink closes the page on the imprint\'s scene: the lockup, the links and the colophon.',
                'examples' => [
                    ['title' => 'As this imprint sets it', 'ground' => 'bare', 'blade' => <<<'BLADE'
<x-site.footer />
BLADE],
                ],
            ],
            'og-image' => [
                'name' => 'OG image',
                'group' => 'Images',
                'from' => 'foundry',
                'description' => 'The 1200 by 630 image a shared link shows, rendered by OG Kit per page and by foundry:assets as the fallback.',
                'examples' => [
                    ['title' => 'Heading, marker, lede and eyebrow', 'ground' => 'bare', 'zoom' => 0.6, 'blade' => <<<'BLADE'
<x-site.og-image heading="A heading with its phrase marked." marked="phrase marked." lede="The lede under it, as long as a line or two." eyebrow="An eyebrow" />
BLADE],
                ],
            ],
            'readme-banner' => [
                'name' => 'README banner',
                'group' => 'Images',
                'from' => 'foundry',
                'description' => 'The 1600 by 520 banner a README opens on, in a light source and a dark one.',
                'examples' => [
                    ['title' => 'Light', 'ground' => 'bare', 'zoom' => 0.45, 'blade' => <<<'BLADE'
<x-site.readme-banner heading="A heading for the README." lede="What the repository holds, in a sentence." />
BLADE],
                    ['title' => 'Dark', 'ground' => 'bare', 'zoom' => 0.45, 'blade' => <<<'BLADE'
<x-site.readme-banner heading="A heading for the README." lede="What the repository holds, in a sentence." dark />
BLADE],
                ],
            ],
            'brand-assets' => [
                'name' => 'Brand assets',
                'group' => 'Images',
                'from' => 'foundry',
                'description' => 'What foundry:assets renders, as /design shows it: the social images, or the icons with the files written beside them.',
                'examples' => [
                    ['title' => 'Icons', 'blade' => <<<'BLADE'
<x-site.brand-assets group="icon" />
BLADE],
                ],
            ],
        ];
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
}
