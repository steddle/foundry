@props(['href', 'icon' => null, 'count' => null, 'current' => null, 'navigate' => true])

{{--
    One link in foundry:app.sidebar, over flux:sidebar.item: an icon, the
    label and a count, the current one on a lichen rule and marked
    aria-current="page".

    @prop href Where it leads.
    @prop icon A Flux icon's name, before the label.
    @prop count A figure after the label, such as what waits behind it; none at 0.
    @prop current Whether it is the page the reader is on; without it, whether its path is the current one exactly, so a section over its own pages or a link with a query passes it.
    @prop navigate Follows the link with Livewire's wire:navigate, as an app on the rail does; false for a full page load, such as a download or another site.
--}}
@php
    $current ??= rtrim(url($href), '/') === rtrim(url()->current(), '/');

    if ($navigate) {
        $attributes = $attributes->merge(['wire:navigate' => true]);
    }
@endphp

{{--
    The rail is always ink, so the rule takes 300, the lichen on ink. Flux draws the current item's
    edge as a border, which moves its icon and count by a pixel; an inset ring draws it in place.
--}}
<flux:sidebar.item :$href :$icon :badge="$count ?: null" :$current :accent="false" :aria-current="$current ? 'page' : null"
    {{ $attributes->class('[&_[data-flux-navlist-badge]]:tabular-nums data-current:border-0! data-current:ring-1 data-current:ring-inset data-current:ring-white/10 data-current:before:absolute data-current:before:inset-y-2 data-current:before:start-0 data-current:before:w-0.5 data-current:before:rounded-full data-current:before:bg-primary-300') }}>{{ $slot }}</flux:sidebar.item>
