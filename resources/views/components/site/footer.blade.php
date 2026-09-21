@props([
    'scene',
    'sceneClass' => 'object-bottom-right',
    'scrim',
    'flatScrim',
    'links' => [],
    'home' => '/',
    'homeLabel' => null,
    'endorsed' => true,
])

@php
    $homeLabel ??= config('imprint.name').', home';
@endphp

{{--
    Ink closes the page, on the imprint's own scene. The slot is the page's
    closing section, set on the same scene. `scrim` is measured against the
    tall band a closing section makes; without one only the scene's floor
    shows, so `flatScrim` keeps the links legible. `items` replaces the list
    `links` would make. Below the rule, the imprint's disclaimer and its
    copyright, and the house that serves it where the lockup is endorsed.
--}}
<footer class="relative isolate overflow-hidden bg-inverse ink">
    <x-site.scene :name="$scene" :class="$sceneClass" :scrim="$slot->hasActualContent() ? $scrim : $flatScrim" />

    <div class="relative">
        {{ $slot }}

        <x-site.container class="flex flex-col gap-10 py-14">
            <div class="flex flex-wrap items-center justify-between gap-8">
                <a href="{{ $home }}" aria-label="{{ $homeLabel }}" class="shrink-0 text-strong">
                    <x-site.lockup :endorsed="$endorsed" class="h-8" />
                </a>
                <ul role="list" class="flex flex-wrap gap-x-7 gap-y-2">
                    @isset($items)
                        {{ $items }}
                    @else
                        @foreach ($links as $label => $href)
                            <li><a href="{{ $href }}" class="hover:text-strong">{{ $label }}</a></li>
                        @endforeach
                    @endisset
                </ul>
            </div>
            <div class="flex flex-wrap items-baseline justify-between gap-x-8 gap-y-3 border-t border-hairline-inverse pt-6">
                <x-site.text variant="small" tone="muted">{{ __(config('imprint.disclaimer')) }}</x-site.text>
                <x-site.text variant="small" tone="muted" class="flex flex-wrap items-baseline gap-x-2 tabular-nums">
                    <span>© {{ now()->year }} {{ config('imprint.name') }}</span>
                    @if ($endorsed)
                        <span aria-hidden="true">|</span>
                        <span>
                            {{ __('foundry::footer.service_by') }}
                            {{-- Steddle's name in its own face, Spectral 700. --}}
                            <a href="https://steddle.com" target="_blank" rel="noopener" class="font-serif font-bold tracking-[-0.03em] text-strong hover:underline">Steddle</a>
                        </span>
                    @endif
                </x-site.text>
            </div>
        </x-site.container>
    </div>
</footer>
