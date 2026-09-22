@props(['eyebrow' => null, 'title', 'lead' => null, 'features', 'sunken' => false])

{{--
    What something offers, side by side: three features to a ruled row,
    numbered, stacked on a phone.

    @group Sections
    @prop eyebrow A label in the accent above the title.
    @prop title The section's h2, at size 1.
    @prop lead The lede, beside the title on a wide screen.
    @prop features A list of [title, body], or of rows keyed `title` and `body`.
    @prop sunken Sets the section a step below the page.
    @slot actions The buttons under the rest.
    @slot slot What follows the rest of the section, in the same band.

    @example Three features
    @ground bare
    <foundry:sections.features eyebrow="What it offers" title="Three things, each checkable." :features="[
        ['Every text', 'Each version a law has had, from the register itself.'],
        ['Any day', 'The text that applied on the day you name.'],
        ['Its source', 'The document it came from, to the byte.'],
    ]" lead="One sentence on why these three matter together." />
--}}
<foundry:section :$sunken {{ $attributes }}>
    <foundry:section-head :$eyebrow :$title :$lead />

    <ol role="list" class="grid grid-cols-1 border-t border-zinc-200 dark:border-zinc-700 lg:grid-cols-3">
        @foreach ($features as $feature)
            @php([$feature, $body] = array_values($feature))
            <li class="flex flex-col gap-3 border-b border-zinc-200 dark:border-zinc-700 py-8 lg:border-b-0 lg:px-8 lg:first:pl-0 lg:last:pr-0 lg:[&:not(:first-child)]:border-l">
                <foundry:text variant="meta" tone="muted" data-markdown-skip>{{ sprintf('%02d', $loop->iteration) }}</foundry:text>
                <foundry:heading size="2" level="3" class="mt-1">{{ $feature }}</foundry:heading>
                <foundry:text class="max-w-[44ch]">{{ $body }}</foundry:text>
            </li>
        @endforeach
    </ol>

    @isset($actions)
        <foundry:actions>{{ $actions }}</foundry:actions>
    @endisset
    {{ $slot }}
</foundry:section>
