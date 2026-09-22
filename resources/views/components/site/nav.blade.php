@props([
    'links' => [],
    'home' => '/',
    'homeLabel' => null,
    'menu' => true,
    'menuLabel' => 'Menu',
    'user' => null,
])

{{--
    The bar every page lays over the hero, which carries the ink: the lockup,
    the links, the language switch where the imprint speaks more than one,
    and the actions. The lab's own pages take the lab's bar, and wherever the
    lab is registered, which is never in production, the links end on it.

    @group Shell
    @prop links label => href, shown from lg up.
    @prop home Where the lockup leads.
    @prop homeLabel The lockup link's accessible name; without one, `foundry::nav.home` with the imprint's name.
    @prop menu Below lg, folds the links, the language switch and the actions into a panel under the bar, through Alpine, which Livewire loads on the page. Off, the lockup, the switch and the actions are the whole bar there and it needs no script.
    @prop menuLabel The accessible name of the button that opens the panel.
    @prop user An object with `name` and `email` for a signed-in reader, whose account menu then closes the bar at every width.
    @slot actions What closes the bar, after the language switch.
    @slot account The imprint's items in the account menu, each a `flux:menu.item`.

    @example As this imprint sets it
    @ground bare
    <div class="relative h-20 bg-zinc-900 ink">
        <x-site.nav />
    </div>
--}}
@php
    $homeLabel ??= __('foundry::nav.home', ['name' => config('imprint.name')]);
    $lab = request()->routeIs('foundry.lab', 'foundry.design', 'foundry.components');

    if (! $lab && Route::has('foundry.lab')) {
        $links['Lab'] = route('foundry.lab');
    }
@endphp

@if ($lab)
<x-foundry::site.lab-nav :home="$home" :home-label="$homeLabel" />
@else
<header class="absolute inset-x-0 top-0 z-10 ink">
    <nav aria-label="Main" @if ($menu) x-data="{ open: false }" @keydown.escape.window="open = false" @endif>
        <x-site.container class="flex items-center justify-between gap-6 py-5">
            <a href="{{ $home }}" aria-label="{{ $homeLabel }}" class="shrink-0 text-zinc-950 dark:text-zinc-50">
                <x-site.lockup class="h-5" />
            </a>

            <div class="flex items-center gap-5">
                <div @class(['flex items-center gap-7', 'max-lg:hidden' => $menu])>
                    @foreach ($links as $label => $href)
                        <a href="{{ $href }}" class="text-copy font-medium whitespace-nowrap hover:text-zinc-950 dark:hover:text-zinc-50 max-lg:hidden">{{ $label }}</a>
                    @endforeach
                    @if (\Steddle\Foundry\Locales::multilingual())
                        <x-site.locale-switch />
                    @endif
                    {{ $actions ?? '' }}
                </div>

                @if ($user)
                    <x-site.account-menu :user="$user">{{ $account ?? '' }}</x-site.account-menu>
                @endif

                @if ($menu)
                    <button type="button" aria-controls="mobile-menu" :aria-expanded="open" aria-label="{{ $menuLabel }}" @click="open = ! open"
                        class="group relative -mr-2 shrink-0 cursor-pointer rounded-md p-2 text-zinc-950 dark:text-zinc-50 hover:bg-zinc-50/5 lg:hidden">
                        <span class="absolute top-1/2 left-1/2 size-[max(100%,3rem)] -translate-1/2 pointer-fine:hidden" aria-hidden="true"></span>
                        <svg class="size-6 group-aria-expanded:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" aria-hidden="true">
                            <path d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg class="size-6 not-group-aria-expanded:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" aria-hidden="true">
                            <path d="M18 6 6 18M6 6l12 12" />
                        </svg>
                    </button>
                @endif
            </div>
        </x-site.container>

        @if ($menu)
            <div id="mobile-menu" x-cloak x-show="open" @click="$event.target.closest('a') && (open = false)"
                x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="-translate-y-2 opacity-0"
                x-transition:leave="transition duration-150 ease-in" x-transition:leave-end="-translate-y-2 opacity-0"
                class="border-y border-zinc-50/13 bg-zinc-900 lg:hidden">
                <x-site.container class="flex flex-col items-start gap-6 py-6">
                    <ul role="list" class="flex w-full flex-col">
                        @foreach ($links as $label => $href)
                            <li><a href="{{ $href }}" class="flex py-2.5 text-lede font-medium hover:text-zinc-950 dark:hover:text-zinc-50">{{ $label }}</a></li>
                        @endforeach
                    </ul>
                    <div class="flex w-full items-center justify-between gap-6">
                        @if (\Steddle\Foundry\Locales::multilingual())
                            <x-site.locale-switch />
                        @endif
                        {{ $actions ?? '' }}
                    </div>
                </x-site.container>
            </div>
        @endif
    </nav>
</header>
@endif
