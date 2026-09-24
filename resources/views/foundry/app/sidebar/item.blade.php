@props(['href', 'icon' => null, 'count' => null, 'current' => null])

{{--
    One link in foundry:app.sidebar, over flux:sidebar.item: an icon, the
    label and a count, the current one on a lichen rule and marked
    aria-current="page".

    @prop href Where it leads.
    @prop icon A Flux icon's name, before the label.
    @prop count A figure after the label, such as what waits behind it; none at 0.
    @prop current Whether it is the page the reader is on; without it, whether its path is the current one exactly, so a section over its own pages or a link with a query passes it.
--}}
@php
    $current ??= rtrim(url($href), '/') === rtrim(url()->current(), '/');
@endphp

{{-- The rail is always ink, so the rule takes 300, the lichen on ink. --}}
<flux:sidebar.item :$href :$icon :badge="$count ?: null" :$current :accent="false" :aria-current="$current ? 'page' : null"
    {{ $attributes->class('[&_[data-flux-navlist-badge]]:tabular-nums data-current:before:absolute data-current:before:inset-y-2 data-current:before:start-0 data-current:before:w-0.5 data-current:before:rounded-full data-current:before:bg-primary-300') }}>{{ $slot }}</flux:sidebar.item>
