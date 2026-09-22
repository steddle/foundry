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

    <dl @class([
        'grid grid-cols-1 border-t border-zinc-200 dark:border-zinc-700',
        match (count($features)) { 2 => 'lg:grid-cols-2', 4 => 'lg:grid-cols-4', default => 'lg:grid-cols-3' },
    ])>
        @foreach ($features as [$feature, $body])
            <div class="flex flex-col gap-3 border-b border-zinc-200 dark:border-zinc-700 py-8 lg:border-b-0 lg:px-8 lg:first:pl-0 lg:last:pr-0 lg:[&:not(:first-child)]:border-l">
                <dt class="flex flex-col gap-4">
                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400 tabular-nums" data-markdown-skip>{{ sprintf('%02d', $loop->iteration) }}</p>
                    <x-site.heading size="2" level="3">{{ $feature }}</x-site.heading>
                </dt>
                <dd class="max-w-[44ch] text-copy text-pretty">{{ $body }}</dd>
            </div>
        @endforeach
    </dl>
</x-site.section>
