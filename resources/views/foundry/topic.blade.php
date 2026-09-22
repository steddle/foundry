@props(['icon', 'eyebrow', 'title', 'href', 'links' => [], 'more' => null])

{{--
    One tile of a tile-grid: an icon and a count, the topic's name and what it
    covers from the slot, then its first links. Its three rows are the grid's
    own, so the rule above the links lines up across a row whatever each
    description's length.

    @group Elements
    @prop icon The name of the Flux icon beside the eyebrow, drawn micro.
    @prop eyebrow The line beside the icon, a count such as `6 articles`.
    @prop title The topic's name, an h2 at size 2, linking to `href` with the description under it.
    @prop href The topic's own page.
    @prop links label => href, the topic's first links under a rule.
    @prop more The label of a last link, to `href`.

    @example In a grid of one
    <foundry:tile-grid :count="1">
        <foundry:topic icon="rocket-launch" eyebrow="2 articles" title="Getting started" href="#" :links="['What it is' => '#', 'Install' => '#']">What it answers, and the ways to ask it.</foundry:topic>
    </foundry:tile-grid>
--}}
<div {{ $attributes->class('row-span-3 grid grid-rows-subgrid gap-5 bg-zinc-25 p-6 sm:p-8 dark:bg-zinc-800') }}>
    <p class="flex items-center gap-2 text-label text-zinc-600 dark:text-zinc-400">
        <flux:icon :icon="$icon" variant="micro" class="fill-zinc-600 dark:fill-zinc-400" />
        {{ $eyebrow }}
    </p>
    <a href="{{ $href }}" class="group flex flex-col gap-2">
        <foundry:heading size="2" level="2" class="group-hover:text-primary-700 dark:group-hover:text-primary-300">{{ $title }}</foundry:heading>
        <foundry:text>{{ $slot }}</foundry:text>
    </a>
    @if ($links || $more)
        <ul role="list" class="flex flex-col gap-2 self-start border-t border-zinc-200 pt-5 dark:border-zinc-700">
            @foreach ($links as $label => $url)
                <li><a href="{{ $url }}" class="text-copy hover:text-zinc-950 dark:hover:text-zinc-50">{{ $label }}</a></li>
            @endforeach
            @if ($more)
                <li><a href="{{ $href }}" class="text-copy font-medium text-primary-700 hover:text-primary-800 dark:text-primary-300 dark:hover:text-primary-200">{{ $more }}</a></li>
            @endif
        </ul>
    @endif
</div>
