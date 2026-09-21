@props([
    'heading',
    'lede' => null,
    'marked' => null,
    'eyebrow' => null,
    'dark' => false,
])

{{-- 1600×520, for a README's <picture>: a light source and a dark one, which GitHub picks by the reader's theme. --}}
<div @class([
    'relative flex h-[520px] w-[1600px] flex-col overflow-hidden p-[64px] font-sans',
    'grain ink bg-inverse' => $dark,
    'bg-page' => ! $dark,
])>
    <x-site.mark class="absolute top-[96px] -right-[40px] size-[560px] text-strong opacity-[0.05]" />

    <x-site.lockup class="relative h-[40px] self-start text-[44px] text-strong" />

    <div class="relative mt-[88px] flex max-w-[900px] flex-col">
        <p class="text-pretty font-serif text-[64px] leading-[1.08] font-semibold tracking-[-0.03em] text-strong"><x-site.marked :text="$heading" :marked="$marked" /></p>
        @if ($lede)
            <p class="mt-[24px] max-w-[700px] text-[20px] leading-[1.5] text-body">{{ $lede }}</p>
        @endif
    </div>
</div>
