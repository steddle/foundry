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

    @group Brand
    @prop heading The image's headline, in Spectral at 72px.
    @prop lede The line under the heading.
    @prop eyebrow A label in the accent, opposite the lockup.
    @prop marked The one phrase of the heading that carries the marker.
    @prop width The canvas's width in pixels, for a format of another size.
    @prop height The canvas's height in pixels, for a format of another size.
    @slot footer A row below the lede, at the canvas's foot.

    @example Heading, marker, lede and eyebrow
    @ground bare
    <foundry:og-image heading="A heading with its phrase marked." marked="phrase marked." lede="The lede under it, as long as a line or two." eyebrow="An eyebrow" />
--}}
<div class="grain ink relative flex flex-col justify-between overflow-hidden bg-zinc-900 p-[64px] font-sans" style="width: {{ $width }}px; height: {{ $height }}px">
    <foundry:mark class="absolute -right-[60px] -bottom-[70px] size-[400px] text-zinc-950 dark:text-zinc-50 opacity-[0.06]" />

    <div class="relative flex items-center justify-between">
        <foundry:lockup class="h-[40px] text-zinc-950 dark:text-zinc-50" />
        @if ($eyebrow)
            <p class="text-[15px] font-semibold text-primary-700 dark:text-primary-300">{{ $eyebrow }}</p>
        @endif
    </div>

    <div class="relative flex flex-col">
        <p class="max-w-[940px] text-pretty font-serif text-[72px] leading-[1.04] font-semibold tracking-[-0.03em] text-zinc-950 dark:text-zinc-50"><foundry:marker :text="$heading" :marked="$marked" /></p>
        @if ($lede)
            <p class="mt-[24px] max-w-[780px] text-pretty text-[26px] leading-[1.5] text-zinc-800 dark:text-zinc-200">{{ $lede }}</p>
        @endif
    </div>

    @isset($footer)
        <div class="relative">{{ $footer }}</div>
    @endisset
</div>
