@props(['name', 'scrim', 'eager' => false])

{{-- A scene: the photo an ink band carries behind its content, and the scrim that keeps the content legible on it. $name is a folder under public/ holding home-{768,1280,1672}.{avif,webp}. Attributes land on the img, for its object-position. --}}
<picture data-markdown-skip>
    <source type="image/avif" srcset="/{{ $name }}/home-768.avif 768w, /{{ $name }}/home-1280.avif 1280w, /{{ $name }}/home-1672.avif 1672w" sizes="100vw">
    <img src="/{{ $name }}/home-1280.webp" srcset="/{{ $name }}/home-768.webp 768w, /{{ $name }}/home-1280.webp 1280w, /{{ $name }}/home-1672.webp 1672w" sizes="100vw"
        alt="" aria-hidden="true" decoding="async" @if ($eager) fetchpriority="high" @else loading="lazy" @endif
        {{ $attributes->class('pointer-events-none absolute inset-0 -z-10 size-full object-cover') }}>
</picture>
<div class="pointer-events-none absolute inset-0 -z-10 {{ $scrim }}"></div>
