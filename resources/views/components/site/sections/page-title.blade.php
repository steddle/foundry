@props(['eyebrow' => null, 'title', 'lead' => null])

{{--
    The first band of a page with no hero, under the bar on bone: its
    eyebrow, title and lede, then the slot in the same band.

    @group Sections
    @prop eyebrow A label in the accent above the title.
    @prop title The page's h1, at size 1.
    @prop lead The lede under the title.

    @example A title and a lede
    @ground bare
    <x-site.sections.page-title title="Lab." lead="The design system, every component the site renders, and the questions still open." />
--}}
<x-site.section {{ $attributes }}>
    <div class="flex flex-col gap-4">
        @if ($eyebrow)
            <x-site.text variant="label" tone="accent">{{ $eyebrow }}</x-site.text>
        @endif
        <x-site.heading size="1" level="1" class="max-w-[20ch]">{{ $title }}</x-site.heading>
        @if ($lead)
            <x-site.text variant="lede" class="max-w-[60ch]">{{ $lead }}</x-site.text>
        @endif
    </div>
    {{ $slot }}
</x-site.section>
