@props(['title', 'lead' => null, 'scene'])

{{--
    A call to act, on a scene: the title, the lede and the actions the slot
    holds, on the left half, clear of the scene's subject, which every scene
    keeps to the right. The page's last word belongs in the footer, as
    x-site.closing; this stands between sections.

    @group Sections

    @example On a scene
    @ground bare
    @zoom 0.5
    <x-site.sections.cta :scene="array_key_first(config('imprint.scenes'))" title="Have a harm and a jurisdiction?" lead="We come back with a read on the class and what it takes to build the book.">
        <x-site.button href="#">Talk to us</x-site.button>
    </x-site.sections.cta>
--}}
<x-site.section :$scene {{ $attributes }}>
    <div class="flex max-w-[34rem] flex-col items-start gap-6">
        <x-site.heading size="1" level="2">{{ $title }}</x-site.heading>
        @if ($lead)
            <x-site.text variant="lede">{{ $lead }}</x-site.text>
        @endif
        @if ($slot->hasActualContent())
            <x-site.actions>{{ $slot }}</x-site.actions>
        @endif
    </div>
</x-site.section>
