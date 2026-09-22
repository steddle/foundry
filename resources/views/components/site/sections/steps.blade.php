@props(['eyebrow' => null, 'title', 'lead' => null, 'steps', 'sunken' => false])

{{--
    How something runs, in steps: a ledger where the numeral carries the row
    and a rule closes it.

    @group Sections
    @prop eyebrow A label in the accent above the title.
    @prop title The section's h2, at size 1.
    @prop lead The lede, beside the title on a wide screen.
    @prop steps A list of [title, body], or of rows keyed `title` and `body`.
    @prop sunken Sets the section a step below the page.
    @slot slot What follows the rest of the section, in the same band.

    @example Three steps
    @ground bare
    <x-site.sections.steps eyebrow="How it works" title="One team, from the first claimant to the transfer." :steps="[
        ['Find the case', 'Harm a court or regulator has already established.'],
        ['Build the book', 'Every claimant verified before they count.'],
        ['Run it', 'Notices, objections and the transfer, to counsel\'s specification.'],
    ]" />
--}}
<x-site.section :$sunken {{ $attributes }}>
    <x-site.section-head :$eyebrow :$title :$lead />

    <ol role="list" class="flex flex-col border-t border-zinc-200 dark:border-zinc-700">
        @foreach ($steps as $step)
            @php([$step, $body] = array_values($step))
            <li class="grid gap-3 border-b border-zinc-200 dark:border-zinc-700 py-8 sm:grid-cols-[5rem_minmax(0,1fr)] lg:grid-cols-[8rem_20rem_minmax(0,1fr)] lg:gap-10">
                <x-site.heading size="figure" tone="accent" data-markdown-skip>{{ sprintf('%02d', $loop->iteration) }}</x-site.heading>
                <x-site.heading size="2" level="3" class="sm:pt-3">{{ $step }}</x-site.heading>
                <x-site.text class="max-w-[60ch] sm:col-start-2 lg:col-start-3 lg:pt-3">{{ $body }}</x-site.text>
            </li>
        @endforeach
    </ol>

    {{ $slot }}
</x-site.section>
