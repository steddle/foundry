@props([
    'heading',
    'lede' => null,
    'eyebrow' => null,
    'marked' => null,
    'width' => 1200,
    'height' => 630,
])

{{--
    OG Kit fetches the page's own URL and renders whatever sits in
    `<template data-og-template>`, at a fixed 1200×630: the page's type scale
    is fluid and would not mean the same thing there, so every size here is a
    literal pixel value instead of the site's `text-heading-*` tokens.
    `marked` is the one phrase of the heading that carries the marker.
    `width` and `height` set the canvas, for a format of another size, and a
    `footer` slot sets a row below the lede.

    @group Brand

    @example Heading, marker, lede and eyebrow
    @ground bare
    @zoom 0.6
    <x-site.og-image heading="A heading with its phrase marked." marked="phrase marked." lede="The lede under it, as long as a line or two." eyebrow="An eyebrow" />
--}}
<div class="grain ink relative flex flex-col justify-between overflow-hidden bg-zinc-900 p-[64px] font-sans" style="width: {{ $width }}px; height: {{ $height }}px">
    <x-site.mark class="absolute -right-[60px] -bottom-[70px] size-[400px] text-zinc-950 dark:text-zinc-50 opacity-[0.06]" />

    <div class="relative flex items-center justify-between">
        <x-site.lockup class="h-[40px] text-zinc-950 dark:text-zinc-50" />
        @if ($eyebrow)
            <p class="text-[15px] font-semibold text-primary-700 dark:text-primary-300">{{ $eyebrow }}</p>
        @endif
    </div>

    <div class="relative flex flex-col">
        <p class="max-w-[940px] text-pretty font-serif text-[72px] leading-[1.04] font-semibold tracking-[-0.03em] text-zinc-950 dark:text-zinc-50"><x-site.marker :text="$heading" :marked="$marked" /></p>
        @if ($lede)
            <p class="mt-[24px] max-w-[780px] text-pretty text-[26px] leading-[1.5] text-zinc-800 dark:text-zinc-200">{{ $lede }}</p>
        @endif
    </div>

    @isset($footer)
        <div class="relative">{{ $footer }}</div>
    @endisset
</div>
