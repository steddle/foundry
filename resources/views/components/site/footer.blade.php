@props([
    'scene',
    'links' => [],
    'home' => '/',
    'homeLabel' => null,
])

{{--
    Ink closes the page on the imprint's scene: the lockup, the links and the
    colophon.

    @group Bands

    @example As this imprint sets it
    @ground bare
    <x-site.footer />
--}}
@php
    $homeLabel ??= config('imprint.name').', home';
    $endorsed = config('imprint.endorsed', true);
@endphp

{{--
    Ink closes the page, on the imprint's own scene. The slot is the page's
    closing section, set on the same scene. The graded scrim is measured
    against the tall band a closing section makes; without one only the
    scene's floor shows, under a flat scrim. Measured on 2026-09-21 against
    the brightest pixel behind each word, over all three imprints' scenes at
    1440 and 390 wide: every line of the footer at 4.6:1 or more. `items` replaces the list
    `links` would make. Below the rule, the imprint's disclaimer and its
    copyright, and the house that serves it where the imprint is endorsed.
--}}
<footer class="relative isolate overflow-hidden bg-zinc-900 ink">
    <x-site.scene :name="$scene" :scrim="$slot->hasActualContent()
        ? 'bg-[linear-gradient(to_right,--alpha(var(--color-zinc-900)/78%)_0%,--alpha(var(--color-zinc-900)/50%)_45%,--alpha(var(--color-zinc-900)/10%)_85%),linear-gradient(to_bottom,--alpha(var(--color-zinc-900)/55%)_0%,transparent_35%,--alpha(var(--color-zinc-900)/82%)_65%,--alpha(var(--color-zinc-900)/95%)_100%)]'
        : 'bg-zinc-900/90'" />

    <div class="relative">
        {{ $slot }}

        @if ($slot->hasActualContent())
            <x-site.container><div class="border-t border-zinc-50/13"></div></x-site.container>
        @endif

        <x-site.container class="flex flex-col gap-10 py-14">
            <div class="flex flex-wrap items-center justify-between gap-8">
                <a href="{{ $home }}" aria-label="{{ $homeLabel }}" class="shrink-0 text-zinc-950 dark:text-zinc-50">
                    {{-- An endorsed lockup at h-8 draws its wordmark at 22px in bron and 23px in sendnda; a plain one matches at 22px. --}}
                    <x-site.lockup :endorsed="$endorsed" @class(['h-8' => $endorsed, 'h-5.5' => ! $endorsed]) />
                </a>
                <ul role="list" class="flex flex-wrap gap-x-7 gap-y-2">
                    @isset($items)
                        {{ $items }}
                    @else
                        @foreach ($links as $label => $href)
                            <li><a href="{{ $href }}" class="hover:text-zinc-950 dark:hover:text-zinc-50">{{ $label }}</a></li>
                        @endforeach
                    @endisset
                </ul>
            </div>
            <x-site.service-line class="border-t border-zinc-50/13 pt-6" />
        </x-site.container>
    </div>
</footer>
