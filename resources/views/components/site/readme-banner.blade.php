@props([
    'heading',
    'lede' => null,
    'marked' => null,
    'eyebrow' => null,
    'dark' => false,
])

{{--
    1600×520, for a README's <picture>: a light source and a dark one, which GitHub picks by the reader's theme.

    @group Brand
    @prop heading The banner's headline, in Spectral at 64px.
    @prop lede The line under the heading.
    @prop marked The one phrase of the heading that carries the marker.
    @prop eyebrow Declared and not drawn: the banner shows no eyebrow.
    @prop dark Draws the dark source, ink and grain; without it the light one, on zinc-50.

    @example Light
    @ground bare
    <x-site.readme-banner heading="A heading for the README." lede="What the repository holds, in a sentence." />

    @example Dark
    @ground bare
    <x-site.readme-banner heading="A heading for the README." lede="What the repository holds, in a sentence." dark />
--}}
<div @class([
    'relative flex h-[520px] w-[1600px] flex-col overflow-hidden p-[64px] font-sans',
    'grain ink bg-zinc-900' => $dark,
    'bg-zinc-50' => ! $dark,
])>
    <x-site.mark class="absolute top-[96px] -right-[40px] size-[560px] text-zinc-950 dark:text-zinc-50 opacity-[0.05]" />

    <x-site.lockup class="relative h-[40px] self-start text-zinc-950 dark:text-zinc-50" />

    <div class="relative mt-[88px] flex max-w-[900px] flex-col">
        <p class="text-pretty font-serif text-[64px] leading-[1.08] font-semibold tracking-[-0.03em] text-zinc-950 dark:text-zinc-50"><x-site.marker :text="$heading" :marked="$marked" /></p>
        @if ($lede)
            <p class="mt-[24px] max-w-[700px] text-pretty text-[20px] leading-[1.5] text-zinc-800 dark:text-zinc-200">{{ $lede }}</p>
        @endif
    </div>
</div>
