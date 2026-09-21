@props(['icon', 'eyebrow', 'title', 'href', 'links' => [], 'more' => null])

{{--
    One tile of a tile-grid: an icon and a count, the topic's name and what it
    covers, then its first links, label => href. `more` labels a last link to
    the topic itself. Its three rows are the grid's own, so the rule above the
    links lines up across a row whatever each description's length.

    @group Layout

    @example In a grid of one
    <x-site.tile-grid :count="1">
        <x-site.topic icon="rocket-launch" eyebrow="2 articles" title="Getting started" href="#" :links="['What it is' => '#', 'Install' => '#']">What it answers, and the ways to ask it.</x-site.topic>
    </x-site.tile-grid>
--}}
<div {{ $attributes->class('row-span-3 grid grid-rows-subgrid gap-5 bg-zinc-25 p-6 sm:p-8 dark:bg-zinc-800') }}>
    <p class="flex items-center gap-2 text-label text-zinc-600 dark:text-zinc-400">
        <flux:icon :icon="$icon" variant="micro" class="fill-zinc-600 dark:fill-zinc-400" />
        {{ $eyebrow }}
    </p>
    <a href="{{ $href }}" class="group flex flex-col gap-2">
        <x-site.heading size="2" level="2" class="group-hover:text-primary-700 dark:group-hover:text-primary-300">{{ $title }}</x-site.heading>
        <x-site.text>{{ $slot }}</x-site.text>
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
