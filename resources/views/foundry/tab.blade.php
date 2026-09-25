@props(['label' => null, 'href' => null, 'edge' => 'bottom'])

{{--
    The imprint's tab: its mark on an ink tile and one line beside it, on a
    white plate that sits against an edge, square where it meets it and
    rounded everywhere else, the way a label is sewn into a seam. White on
    bone and on ink alike, so it reads as a thing laid on the page rather
    than a band of it. An entry point, not a logo: the page sets it against
    its own edge, `absolute bottom-0` or `right-0`, and it opens what the
    imprint does for the reader, a chat or a form.

    Its measures are the brand book's, tightened to the family's steps: a
    22px tile at 5px with the mark at 16.5px on it, 8px to the line, 10px of
    padding all round, the line in Chivo at `small`, and 10px of radius on
    the corners away from the edge, the one place the family's 5px ceiling
    gives way.

    @group Brand
    @prop label The one line beside the mark; without one, the imprint's name. Never two.
    @prop href Where the tab leads; without one it is a tab, not a link.
    @prop edge bottom, top, left or right: the edge it sits against, which stays square while the others round. Left and right set the line on its side, reading down.

    @example Against the foot of a band
    <div class="relative flex h-56 items-center justify-center rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900">
        <foundry:tab href="#" class="absolute bottom-0 left-10" />
    </div>

    @example Against the side, reading down
    <div class="relative flex h-56 items-center justify-center rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900">
        <foundry:tab href="#" edge="right" label="Tell us what happened" class="absolute top-8 right-0" />
    </div>

    @example On ink, at the foot of the page
    @ground ink
    <div class="relative flex h-56 items-center justify-center">
        <foundry:tab href="#" label="Tell us what happened" class="absolute bottom-0 left-10" />
    </div>
--}}
@php
    $label ??= config('imprint.name');
    $tag = $href ? 'a' : 'div';
    $sideways = in_array($edge, ['left', 'right'], true);
@endphp

{{-- White and navy in both themes, never a pair: the plate is the same object on either ground. --}}
<{{ $tag }} @if ($href) href="{{ $href }}" @endif {{ $attributes->class([
    // The padding at the flat edge carries the extra pixel the border there left behind.
    'inline-flex items-center gap-2 border border-zinc-200 bg-white text-small font-medium text-zinc-950',
    'flex-col py-2.5' => $sideways,
    'px-2.5' => ! $sideways,
    'rounded-t-[10px] border-b-0 pt-2.5 pb-[11px]' => $edge === 'bottom',
    'rounded-b-[10px] border-t-0 pt-[11px] pb-2.5' => $edge === 'top',
    'rounded-l-[10px] border-r-0 pl-2.5 pr-[11px]' => $edge === 'right',
    'rounded-r-[10px] border-l-0 pr-2.5 pl-[11px]' => $edge === 'left',
    'hover:border-zinc-500' => $href,
]) }}>
    <span class="flex size-[22px] shrink-0 items-center justify-center rounded-[5px] bg-zinc-900 text-zinc-50">
        <foundry:mark class="size-[16.5px]" />
    </span>

    {{-- Against a side the line reads down, as a spine does. --}}
    <span @class(['whitespace-nowrap', '[writing-mode:vertical-rl]' => $sideways, 'rotate-180' => $edge === 'left'])>{{ $label }}</span>
</{{ $tag }}>
