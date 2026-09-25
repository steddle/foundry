@props([
    'links' => [],
    'home' => '/',
    'homeLabel' => null,
    'menu' => true,
    'menuLabel' => null,
    'user' => null,
])

{{--
    The bar every page lays over the ink it opens on, a hero or an
    foundry:app.band: the lockup, the links with the current one marked, the
    language switch where the imprint speaks more than one, the actions and,
    for a signed-in reader, the account menu. An imprint sets it in its
    foundry:bar. The lab's own pages take the lab's bar, and wherever the lab
    is registered, which is never in production, the links end on it.

    @group Shell
    @prop links label => href, shown from lg up. The current link is the one whose address the current one equals or lies under, marked as aria-current="page".
    @prop home Where the lockup leads.
    @prop homeLabel The lockup link's accessible name; without one, `foundry::nav.home` with the imprint's name.
    @prop menu Below lg, folds the links, the language switch and the actions into a panel under the bar, through Alpine, which Livewire loads on the page. Escape closes it, and focus inside it goes back to the button that opened it. Off, the lockup, the switch and the actions are the whole bar there and it needs no script.
    @prop menuLabel The accessible name of the button that opens the panel; without one, `foundry::nav.menu`.
    @prop user An object with `name` and `email` for a signed-in reader, whose account menu then closes the bar at every width.
    @slot actions What closes the bar, after the language switch.
    @slot account The imprint's items in the account menu, each a `flux:menu.item`.

    @example As this imprint sets it
    @ground bare
    <div class="relative h-20 bg-zinc-900 ink">
        <foundry:header />
    </div>
--}}
@php
    $homeLabel ??= __('foundry::nav.home', ['name' => config('imprint.name')]);
    $menuLabel ??= __('foundry::nav.menu');
    $lab = request()->routeIs('foundry.lab', 'foundry.design', 'foundry.components');

    if (! $lab && Route::has('foundry.lab')) {
        $links['Lab'] = route('foundry.lab');
    }

    $here = rtrim(url()->current(), '/');

    // Under a root address every page lies, so a root link is current only on the root itself.
    $current = array_map(function (string $href) use ($here): bool {
        $address = rtrim(url($href), '/');

        return $address === $here || (trim((string) parse_url($address, PHP_URL_PATH), '/') !== '' && str_starts_with($here, $address.'/'));
    }, $links);
@endphp

@if ($lab)
<x-foundry::lab-nav :home="$home" :home-label="$homeLabel" />
@else
<header class="absolute inset-x-0 top-0 z-10 ink">
    <nav aria-label="{{ __('foundry::nav.main') }}" @if ($menu) x-data="{ open: false }" x-id="['site-menu']" @keydown.escape.window="if (open) { open = false; $el.contains(document.activeElement) && $refs.toggle.focus() }" @endif>
        <foundry:container class="flex items-center justify-between gap-6 py-5">
            <a href="{{ $home }}" aria-label="{{ $homeLabel }}" class="shrink-0 text-zinc-950 dark:text-zinc-50">
                <foundry:lockup class="h-5" />
            </a>

            <div class="flex items-center gap-5">
                <div @class(['flex items-center gap-7', 'max-lg:hidden' => $menu])>
                    @foreach ($links as $label => $href)
                        <a href="{{ $href }}" @if ($current[$label]) aria-current="page" @endif class="text-copy font-medium whitespace-nowrap decoration-primary-700 decoration-2 underline-offset-[0.6em] hover:text-zinc-950 aria-[current=page]:text-zinc-950 aria-[current=page]:underline dark:decoration-primary-300 dark:hover:text-zinc-50 dark:aria-[current=page]:text-zinc-50 max-lg:hidden">{{ $label }}</a>
                    @endforeach
                    @if (\Steddle\Foundry\Locales::multilingual())
                        <foundry:locale-switch />
                    @endif
                    {{ $actions ?? '' }}
                </div>

                @if ($user)
                    <foundry:account-menu :user="$user">{{ $account ?? '' }}</foundry:account-menu>
                @endif

                @if ($menu)
                    <flux:button variant="ghost" square x-ref="toggle" x-bind:aria-controls="$id('site-menu')" x-bind:aria-expanded="open" aria-label="{{ $menuLabel }}" x-on:click="open = ! open"
                        class="group -mr-2 shrink-0 cursor-pointer text-zinc-950! dark:text-zinc-50! hover:bg-zinc-50/5! lg:hidden">
                        <span class="absolute top-1/2 left-1/2 size-[max(100%,3rem)] -translate-1/2 pointer-fine:hidden" aria-hidden="true"></span>
                        <svg class="size-6 group-aria-expanded:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" aria-hidden="true">
                            <path d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg class="size-6 not-group-aria-expanded:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" aria-hidden="true">
                            <path d="M18 6 6 18M6 6l12 12" />
                        </svg>
                    </flux:button>
                @endif
            </div>
        </foundry:container>

        @if ($menu)
            <div :id="$id('site-menu')" x-cloak x-show="open" @click="$event.target.closest('a') && (open = false)"
                x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="-translate-y-2 opacity-0"
                x-transition:leave="transition duration-150 ease-in" x-transition:leave-end="-translate-y-2 opacity-0"
                class="border-y border-zinc-50/13 bg-zinc-900 lg:hidden">
                <foundry:container class="flex flex-col items-start gap-6 py-6">
                    <ul role="list" class="flex w-full flex-col">
                        @foreach ($links as $label => $href)
                            <li><a href="{{ $href }}" @if ($current[$label]) aria-current="page" @endif class="flex py-2.5 text-lede font-medium hover:text-zinc-950 aria-[current=page]:text-zinc-950 dark:hover:text-zinc-50 dark:aria-[current=page]:text-zinc-50">{{ $label }}</a></li>
                        @endforeach
                    </ul>
                    <div class="flex w-full items-center justify-between gap-6">
                        @if (\Steddle\Foundry\Locales::multilingual())
                            <foundry:locale-switch />
                        @endif
                        {{ $actions ?? '' }}
                    </div>
                </foundry:container>
            </div>
        @endif
    </nav>
</header>
@endif
