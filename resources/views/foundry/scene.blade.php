@props(['name', 'scrim', 'eager' => false])

{{--
    The photo an ink band carries behind its content, with the scrim that
    keeps the content legible on it.

    @group Brand
    @prop name A folder under public/ holding home-{768,1280,1672}.{avif,webp}; config/imprint.php frames it under `scenes.{name}`, where its subject stays in view as the band narrows.
    @prop scrim start or center, for content set there, or a band's own measured classes.
    @prop eager Loads the photo with high fetch priority rather than lazily, for a scene in the first viewport.

    @example Behind a band
    <div class="relative isolate flex h-72 items-end overflow-hidden bg-zinc-900 ink p-8">
        <foundry:scene :name="array_key_first(config('imprint.scenes'))" scrim="bg-zinc-900/60" />
        <foundry:heading size="2">Content on the scene.</foundry:heading>
    </div>
--}}
@php
    // `start` and `center` are measured over every imprint's scenes at 1440 and 390 wide: each line of text set there reads at 4.5:1 or more. A phone sets text across the whole band, so `start` is flat there.
    $scrim = [
        'start' => 'max-lg:bg-zinc-900/88 lg:bg-[linear-gradient(to_right,--alpha(var(--color-zinc-900)/90%)_0%,--alpha(var(--color-zinc-900)/75%)_45%,--alpha(var(--color-zinc-900)/20%)_90%),linear-gradient(to_bottom,transparent_55%,--alpha(var(--color-zinc-900)/85%)_100%)]',
        'center' => 'bg-[radial-gradient(ellipse_at_center,--alpha(var(--color-zinc-900)/80%)_0%,--alpha(var(--color-zinc-900)/55%)_55%,--alpha(var(--color-zinc-900)/35%)_100%)]',
    ][$scrim] ?? $scrim;
@endphp

<picture data-markdown-skip>
    <source type="image/avif" srcset="/{{ $name }}/home-768.avif 768w, /{{ $name }}/home-1280.avif 1280w, /{{ $name }}/home-1672.avif 1672w" sizes="100vw">
    <img src="/{{ $name }}/home-1280.webp" srcset="/{{ $name }}/home-768.webp 768w, /{{ $name }}/home-1280.webp 1280w, /{{ $name }}/home-1672.webp 1672w" sizes="100vw"
        alt="" aria-hidden="true" decoding="async" @if ($eager) fetchpriority="high" @else loading="lazy" @endif
        {{ $attributes->class(['pointer-events-none absolute inset-0 -z-10 size-full object-cover', config("imprint.scenes.{$name}")]) }}>
</picture>
<div class="pointer-events-none absolute inset-0 -z-10 {{ $scrim }}"></div>
