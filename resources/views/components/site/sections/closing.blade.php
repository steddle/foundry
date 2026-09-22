@props(['eyebrow' => null, 'title', 'lead' => null, 'align' => 'center'])

{{--
    The page's last word, set in the footer's slot on its scene: the
    eyebrow, the title, the lede and the `actions`, stacked. At the start everything stays
    on the dark side of the footer's scrim, clear of the scene's subject,
    which every scene keeps to the right.

    @group Sections

    @example Centred
    @ground ink
    <x-site.sections.closing title="The last thing a reader sees.">
        <x-slot:actions>
            <x-site.button href="#">An action</x-site.button>
        </x-slot:actions>
    </x-site.sections.closing>

    @example At the start, with a lede
    @ground ink
    <x-site.sections.closing align="start" title="Where to go when the docs did not answer." lead="One sentence on what the action does.">
        <x-slot:actions>
            <x-site.button href="#">An action</x-site.button>
        </x-slot:actions>
    </x-site.sections.closing>
--}}
<section {{ $attributes }}>
    <x-site.container @class([
        'flex flex-col gap-8 py-24 lg:py-32',
        'items-center text-center' => $align === 'center',
        'items-start' => $align === 'start',
    ])>
        <div @class(['flex flex-col gap-4', 'items-center' => $align === 'center'])>
            @if ($eyebrow)
                <x-site.text variant="label" tone="accent">{{ $eyebrow }}</x-site.text>
            @endif
            <x-site.heading size="1" level="2" class="max-w-[18ch]">{{ $title }}</x-site.heading>
            @if ($lead)
                <x-site.text variant="lede" class="max-w-[48ch]">{{ $lead }}</x-site.text>
            @endif
        </div>
        @isset($actions)
            <x-site.actions>{{ $actions }}</x-site.actions>
        @endisset
    </x-site.container>
</section>
