@props(['title', 'lead' => null])

{{--
    The first band of a page with no hero, under the bar on bone: its title and lede, then the slot in the same band.

    @group Sections

    @example A title and a lede
    @ground bare
    <x-site.page-title title="Lab." lead="The design system, every component the site renders, and the questions still open." />
--}}
<x-site.section {{ $attributes }}>
    <div class="flex flex-col gap-4">
        <x-site.heading size="1" level="1" class="max-w-[20ch]">{{ $title }}</x-site.heading>
        @if ($lead)
            <x-site.text variant="lede" class="max-w-[60ch]">{{ $lead }}</x-site.text>
        @endif
    </div>
    {{ $slot }}
</x-site.section>
