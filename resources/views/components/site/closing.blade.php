@props(['title', 'lead' => null, 'align' => 'center'])

{{--
    The page's last word, set in the footer's slot on its scene: a title, a
    lede and the actions the slot holds. Centred it stands alone; at the start
    the actions end the row beside the text, and fold under it on a phone.
--}}
<section {{ $attributes }}>
    <x-site.container @class([
        'flex gap-8 py-24 lg:py-32',
        'flex-col items-center text-center' => $align === 'center',
        'flex-wrap items-end justify-between gap-x-12' => $align === 'start',
    ])>
        <div @class(['flex flex-col gap-4', 'items-center' => $align === 'center'])>
            <x-site.heading size="1" level="2" class="max-w-[18ch]">{{ $title }}</x-site.heading>
            @if ($lead)
                <x-site.text variant="lede" class="max-w-[48ch]">{{ $lead }}</x-site.text>
            @endif
        </div>
        @if ($slot->hasActualContent())
            <x-site.actions>{{ $slot }}</x-site.actions>
        @endif
    </x-site.container>
</section>
