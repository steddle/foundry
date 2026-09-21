{{-- A reading layout: navigation beside the text on wide screens, above it on narrow ones. --}}
<div {{ $attributes->class('grid grid-cols-1 gap-10 lg:grid-cols-[15rem_minmax(0,1fr)] lg:gap-12') }}>
    <aside data-markdown-skip class="lg:sticky lg:top-8 lg:self-start">
        {{ $aside }}
    </aside>
    <div class="min-w-0">
        {{ $slot }}
    </div>
</div>
