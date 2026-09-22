@props(['eyebrow', 'title', 'features', 'sunken' => false])

{{--
    What something offers, side by side: up to four features in one ruled
    row, numbered, stacked on a phone. `features` is a list of [title, body];
    the slot is the lede beside the title.

    @group Sections

    @example Three features
    @ground bare
    @zoom 0.5
    <x-site.sections.features eyebrow="What it offers" title="Three things, each checkable." :features="[
        ['Every text', 'Each version a law has had, from the register itself.'],
        ['Any day', 'The text that applied on the day you name.'],
        ['Its source', 'The document it came from, to the byte.'],
    ]">
        One sentence on why these three matter together.
    </x-site.sections.features>
--}}
<x-site.section :$sunken {{ $attributes }}>
    <x-site.section-head :$eyebrow :$title>{{ $slot }}</x-site.section-head>

    <ol role="list" @class([
        'grid grid-cols-1 border-t border-zinc-200 dark:border-zinc-700',
        match (count($features)) { 2 => 'lg:grid-cols-2', 4 => 'lg:grid-cols-4', default => 'lg:grid-cols-3' },
    ])>
        @foreach ($features as [$feature, $body])
            <li class="flex flex-col gap-3 border-b border-zinc-200 dark:border-zinc-700 py-8 lg:border-b-0 lg:px-8 lg:first:pl-0 lg:last:pr-0 lg:[&:not(:first-child)]:border-l">
                <x-site.text variant="meta" tone="muted" data-markdown-skip>{{ sprintf('%02d', $loop->iteration) }}</x-site.text>
                <x-site.heading size="2" level="3" class="mt-1">{{ $feature }}</x-site.heading>
                <x-site.text class="max-w-[44ch]">{{ $body }}</x-site.text>
            </li>
        @endforeach
    </ol>
</x-site.section>
