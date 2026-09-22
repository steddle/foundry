@props(['eyebrow' => null, 'title', 'lead' => null, 'align' => 'center'])

{{--
    The page's last word, set in the footer's slot on its scene: the
    eyebrow, the title, the lede and the actions, stacked.

    @group Sections
    @prop eyebrow A label in the accent above the title.
    @prop title The section's h2, at size 1.
    @prop lead The lede under the title.
    @prop align center or start: at the start everything stays on the dark side of the footer's scrim, clear of the scene's subject, which every scene keeps to the right.
    @slot actions The buttons under the lede.
    @slot slot What follows the rest of the section, in the same band.

    @example Centred
    @ground ink
    <foundry:sections.closing title="The last thing a reader sees.">
        <x-slot:actions>
            <foundry:button href="#">An action</foundry:button>
        </x-slot:actions>
    </foundry:sections.closing>

    @example At the start, with a lede
    @ground ink
    <foundry:sections.closing align="start" title="Where to go when the docs did not answer." lead="One sentence on what the action does.">
        <x-slot:actions>
            <foundry:button href="#">An action</foundry:button>
        </x-slot:actions>
    </foundry:sections.closing>
--}}
<section {{ $attributes }}>
    <foundry:container @class([
        'flex flex-col gap-8 py-24 lg:py-32',
        'items-center text-center' => $align === 'center',
        'items-start' => $align === 'start',
    ])>
        <foundry:section-head stacked :$align :$eyebrow :$title :$lead />
        @isset($actions)
            <foundry:actions>{{ $actions }}</foundry:actions>
        @endisset
        {{ $slot }}
    </foundry:container>
</section>
