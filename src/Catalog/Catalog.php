<?php

namespace Steddle\Foundry\Catalog;

/**
 * Every component /components shows, in the order of its index. An example is
 * Blade the page renders live and prints beside it, so an imprint sees what it
 * renders itself, its own version of a component included. `ground` is the
 * band an example stands on, `page` or `ink`; `zoom` shrinks a fixed-size
 * render to the column; `code` alone prints the Blade without rendering it.
 */
final class Catalog
{
    /**
     * @return array<string, array{name: string, group: string, from: string, description: string, examples: list<array{title: string, blade: string, ground?: string, zoom?: float, code?: bool}>}>
     */
    public static function all(): array
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
    <div class="rounded-md border border-dashed border-subtle p-6 text-muted">Wide, with the phone and desktop gutters.</div>
</x-site.container>
BLADE],
                ],
            ],
            'section' => [
                'name' => 'Section',
                'group' => 'Layout',
                'from' => 'foundry',
                'description' => 'A band of the page, spaced from the next, with a hairline between bands in dark mode.',
                'examples' => [
                    ['title' => 'A band with a heading and a lede', 'blade' => <<<'BLADE'
<x-site.section class="py-0! lg:py-0!">
    <x-site.heading size="2">A section heading.</x-site.heading>
    <x-site.text variant="lede">The lede under it, in the body colour.</x-site.text>
</x-site.section>
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
                'from' => 'imprint',
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
            'lockup' => [
                'name' => 'Lockup',
                'group' => 'Brand',
                'from' => 'imprint',
                'description' => 'The mark and the wordmark in the current colour. An imprint endorses it with BY STEDDLE where it stands without the house around it.',
                'examples' => [
                    ['title' => 'On the page', 'blade' => <<<'BLADE'
<x-site.lockup class="h-8 text-strong" />
BLADE],
                    ['title' => 'On ink', 'ground' => 'ink', 'blade' => <<<'BLADE'
<x-site.lockup class="h-8 text-strong" />
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
<div class="flex items-end gap-6 text-strong">
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
            'page-hero' => [
                'name' => 'Page hero',
                'group' => 'Bands',
                'from' => 'imprint',
                'description' => 'The ink band that opens every page but the home page. The header overlays it.',
                'examples' => [
                    ['title' => 'Title and lead', 'blade' => <<<'BLADE'
<x-site.page-hero title="A page title." lead="The lead that says what the page is for, in a sentence or two." />
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
<div class="relative isolate flex h-72 items-end overflow-hidden bg-inverse ink p-8">
    <x-site.scene name="footer" class="object-bottom-right" scrim="bg-inverse/60" />
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
<div class="relative h-20 bg-inverse ink">
    <x-site.nav />
</div>
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
     * @return array<string, list<string>> group => slugs, in index order
     */
    public static function groups(): array
    {
        return collect(self::all())->map(fn (array $entry, string $slug): array => [$entry['group'], $slug])
            ->groupBy(0)
            ->map(fn ($pairs) => $pairs->pluck(1)->all())
            ->all();
    }
}
