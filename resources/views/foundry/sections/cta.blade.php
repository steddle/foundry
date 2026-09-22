@props(['eyebrow' => null, 'title', 'lead' => null, 'scene'])

{{--
    A call to act, on a scene: the eyebrow, the title, the lede and the
    actions, on the left half, clear of the scene's subject, which every
    scene keeps to the right. The page's last word belongs in the footer, as
    foundry:sections.closing; this stands between sections.

    @group Sections
    @prop eyebrow A label in the accent above the title.
    @prop title The section's h2, at size 1.
    @prop lead The lede under the title.
    @prop scene The scene the band is ink over, by its name in config/imprint.php.
    @slot actions The buttons under the lede.
    @slot slot What follows the rest of the section, in the same band.

    @example On a scene
    @ground bare
    <foundry:sections.cta :scene="array_key_first(config('imprint.scenes'))" title="Have a harm and a jurisdiction?" lead="We come back with a read on the class and what it takes to build the book.">
        <x-slot:actions>
            <foundry:button href="#">Talk to us</foundry:button>
        </x-slot:actions>
    </foundry:sections.cta>
--}}
<foundry:section :$scene {{ $attributes }}>
    <div class="flex max-w-[34rem] flex-col items-start gap-6">
        <foundry:section-head stacked :$eyebrow :$title :$lead />
        @isset($actions)
            <foundry:actions>{{ $actions }}</foundry:actions>
        @endisset
        {{ $slot }}
    </div>
</foundry:section>
