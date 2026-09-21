{{--
    The line every page closes on: the imprint's disclaimer, its copyright,
    and the house that serves it where `imprint.endorsed` holds, as it does
    for every imprint but Steddle itself. On ink.
--}}
<div {{ $attributes->class('flex flex-wrap items-baseline justify-between gap-x-8 gap-y-3') }}>
    <x-site.text variant="small" tone="muted">{{ __(config('imprint.disclaimer')) }}</x-site.text>
    <x-site.text variant="small" tone="muted" class="flex flex-wrap items-baseline gap-x-2 tabular-nums">
        <span>© {{ now()->year }} {{ config('imprint.name') }}</span>
        @if (config('imprint.endorsed', true))
            <span aria-hidden="true">|</span>
            <span>
                {{ __('foundry::footer.service_by') }}
                {{-- Steddle's name in its own face, Spectral 700. --}}
                <a href="https://steddle.com" target="_blank" rel="noopener" class="font-serif font-bold tracking-[-0.03em] text-zinc-950 dark:text-zinc-50 hover:underline">Steddle</a>
            </span>
        @endif
    </x-site.text>
</div>
