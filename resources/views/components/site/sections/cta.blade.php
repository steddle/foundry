@props(['eyebrow' => null, 'title', 'lead' => null, 'scene'])

{{--
    A call to act, on a scene: the eyebrow, the title, the lede and the
    actions, on the left half, clear of the scene's subject, which every
    scene keeps to the right. The page's last word belongs in the footer, as
    x-site.sections.closing; this stands between sections.

    @group Sections
    @prop eyebrow A label in the accent above the title.
    @prop title The section's h2, at size 1.
    @prop lead The lede under the title.
    @prop scene The scene the band is ink over, by its name in config/imprint.php.
    @slot actions The buttons under the lede.
    @slot slot What follows the rest of the section, in the same band.

    @example On a scene
    @ground bare
    <x-site.sections.cta :scene="array_key_first(config('imprint.scenes'))" title="Have a harm and a jurisdiction?" lead="We come back with a read on the class and what it takes to build the book.">
        <x-slot:actions>
            <x-site.button href="#">Talk to us</x-site.button>
        </x-slot:actions>
    </x-site.sections.cta>
--}}
<x-site.section :$scene {{ $attributes }}>
    <div class="flex max-w-[34rem] flex-col items-start gap-6">
        <x-site.section-head stacked :$eyebrow :$title :$lead />
        @isset($actions)
            <x-site.actions>{{ $actions }}</x-site.actions>
        @endisset
        {{ $slot }}
    </div>
</x-site.section>
