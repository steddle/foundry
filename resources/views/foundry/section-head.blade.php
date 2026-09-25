@props(['eyebrow' => null, 'title', 'lead' => null, 'marked' => null, 'stacked' => false, 'align' => 'start', 'size' => '1', 'level' => 2])

{{--
    The words every section opens on: the eyebrow, the title and the lede,
    in one place so every section sets them alike. Beside each other on a
    wide screen, or stacked, one above the next, where the section sets
    something under them. A div and not a header: a page's markdown drops
    every <header> as chrome.

    @group Type
    @prop eyebrow A label in the accent above the title, or a slot where it is more than a label, a breadcrumb or a status.
    @prop title The heading, with its `marked` phrase laid on the marker.
    @prop lead The lede, as text or as a slot where it holds markup.
    @prop marked The phrase of `title` laid on the marker, on a page's opening title alone.
    @prop stacked Sets the lede under the title in place of beside it.
    @prop align start or center: where stacked words set.
    @prop size display or 1: the title's step of the type scale.
    @prop level 1 or 2: the title's h element.

    @example Beside each other
    <foundry:section-head eyebrow="How it works" title="One title that says what the section argues." lead="The lede, beside the title on a wide screen and under it on a phone." />

    @example Stacked
    <foundry:section-head stacked eyebrow="How it works" title="One title that says what the section argues." lead="The lede, under the title." />
--}}
@php
    $heading = $size === 'display' ? 'max-w-[16ch]' : 'max-w-[20ch]';
@endphp

<div {{ $attributes->class([
    'grid grid-cols-1 items-end gap-6 lg:grid-cols-2 lg:gap-12' => ! $stacked,
    'flex flex-col gap-5' => $stacked,
    'items-start' => $stacked && $align === 'start',
    'items-center text-center' => $stacked && $align === 'center',
]) }}>
    <div @class(['flex flex-col gap-5' => ! $stacked, 'contents' => $stacked])>
        @if ($eyebrow instanceof \Illuminate\View\ComponentSlot)
            {{ $eyebrow }}
        @elseif ($eyebrow)
            <foundry:text variant="label" tone="accent">{{ $eyebrow }}</foundry:text>
        @endif
        <foundry:heading :$size :$level class="{{ $heading }}"><foundry:marker :text="$title" :$marked /></foundry:heading>
    </div>
    @if ($lead)
        <foundry:text variant="lede" class="max-w-[48ch]">{{ $lead }}</foundry:text>
    @endif
</div>
