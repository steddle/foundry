@props(['href' => null, 'title', 'meta' => null])

{{--
    One record in foundry:rows: its title with a status beside it, a line of
    metadata under it, and its actions at the end. With an href the title and
    the metadata are one link over the row; the actions stand outside it, so
    their buttons stay their own.

    @group Elements
    @prop href Where the row leads.
    @prop title The record's name.
    @prop meta A line under the title: dates, terms, a count, in tabular numerals.
    @slot leading An icon or an avatar before the title, inside the link where the row has one.
    @slot status A badge beside the title.
    @slot actions Buttons or a menu at the row's end.

    @example With a status and an action
    <foundry:rows>
        <foundry:record-row href="#" title="Mutual NDA with Northwind" meta="Sent 21 Sep 2026 | 2 years + 3 years confidential">
            <x-slot:status><foundry:badge tone="warning" dot>Awaiting signature</foundry:badge></x-slot:status>
            <x-slot:actions><foundry:button href="#" variant="secondary">Resend</foundry:button></x-slot:actions>
        </foundry:record-row>
    </foundry:rows>

    @example With an icon before the title
    <foundry:rows>
        <foundry:record-row href="#" title="Send NDA" meta="First used 3 Sep 2026 | last used today">
            <x-slot:leading><img src="{{ asset('icon-512.png') }}" alt="" width="40" height="40" class="size-10 rounded-lg"></x-slot:leading>
            <x-slot:actions><foundry:button variant="secondary">Leave</foundry:button></x-slot:actions>
        </foundry:record-row>
    </foundry:rows>
--}}
@php
    $tag = $href ? 'a' : 'div';
@endphp

<li {{ $attributes->class('flex items-center gap-4 hover:bg-zinc-100 dark:hover:bg-zinc-800') }}>
    <{{ $tag }} @if ($href) href="{{ $href }}" @endif class="flex min-w-0 flex-1 items-center gap-3 py-4 pl-3">
        @isset($leading)
            <span class="flex shrink-0">{{ $leading }}</span>
        @endisset

        <span class="flex min-w-0 flex-col gap-1">
            <span class="flex flex-wrap items-center gap-x-3 gap-y-1">
                <span class="font-medium text-zinc-950 dark:text-zinc-50">{{ $title }}</span>
                {{ $status ?? '' }}
            </span>
            @if ($meta)
                <span class="text-small tabular-nums text-zinc-600 dark:text-zinc-400">{{ $meta }}</span>
            @endif
        </span>
    </{{ $tag }}>

    @isset($actions)
        <div class="flex shrink-0 items-center gap-2 pr-3">{{ $actions }}</div>
    @endisset
</li>
