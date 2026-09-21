@props([
    'links' => [],
    'home' => '/',
    'homeLabel' => null,
    'menu' => true,
    'menuLabel' => 'Menu',
])

@php
    $homeLabel ??= config('imprint.name').', home';
@endphp

{{--
    Overlays the hero, which carries the ink. `actions` closes the bar. With
    `menu`, a phone folds the links and the actions into a panel below the bar,
    through Alpine, which Livewire loads on the page; without one, the lockup
    and the actions are the whole bar there and the bar needs no script.
--}}
<header class="absolute inset-x-0 top-0 z-10 ink">
    <nav aria-label="Main" @if ($menu) x-data="{ open: false }" @keydown.escape.window="open = false" @endif>
        <x-site.container class="flex items-center justify-between gap-6 py-5">
            <a href="{{ $home }}" aria-label="{{ $homeLabel }}" class="shrink-0 text-strong">
                <x-site.lockup class="h-6" />
            </a>

            <div @class(['flex items-center gap-7', 'max-lg:hidden' => $menu])>
                @foreach ($links as $label => $href)
                    <a href="{{ $href }}" class="text-copy font-medium whitespace-nowrap hover:text-strong max-lg:hidden">{{ $label }}</a>
                @endforeach
                {{ $actions ?? '' }}
            </div>

            @if ($menu)
                <button type="button" aria-controls="mobile-menu" :aria-expanded="open" aria-label="{{ $menuLabel }}" @click="open = ! open"
                    class="group relative -mr-2 shrink-0 cursor-pointer rounded-md p-2 text-strong hover:bg-strong/5 lg:hidden">
                    <span class="absolute top-1/2 left-1/2 size-[max(100%,3rem)] -translate-1/2 pointer-fine:hidden" aria-hidden="true"></span>
                    <svg class="size-6 group-aria-expanded:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" aria-hidden="true">
                        <path d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg class="size-6 not-group-aria-expanded:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" aria-hidden="true">
                        <path d="M18 6 6 18M6 6l12 12" />
                    </svg>
                </button>
            @endif
        </x-site.container>

        @if ($menu)
            <div id="mobile-menu" x-cloak x-show="open" @click="$event.target.closest('a') && (open = false)"
                x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="-translate-y-2 opacity-0"
                x-transition:leave="transition duration-150 ease-in" x-transition:leave-end="-translate-y-2 opacity-0"
                class="border-y border-hairline-inverse bg-inverse lg:hidden">
                <x-site.container class="flex flex-col items-start gap-6 py-6">
                    <ul role="list" class="flex w-full flex-col">
                        @foreach ($links as $label => $href)
                            <li><a href="{{ $href }}" class="flex py-2.5 text-lede font-medium hover:text-strong">{{ $label }}</a></li>
                        @endforeach
                    </ul>
                    <div class="flex w-full items-center justify-between gap-6">
                        {{ $actions ?? '' }}
                    </div>
                </x-site.container>
            </div>
        @endif
    </nav>
</header>
