@props([
    'links' => [],
    'user',
    'home' => null,
    'homeLabel' => null,
    'menuLabel' => null,
])

{{--
    The bar over a signed-in page, in flow on the page's bone and ruled off
    below it: the lockup, the links with the current one marked, the actions
    and the account menu. Below lg the links and the actions fold into a
    panel under the bar, through Alpine, which Livewire loads on the page; the
    account menu stays in the bar.

    @group Shell
    @prop links label => href, shown from lg up. The current link is the one whose address the current one equals or lies under, marked by a rule in the accent and aria-current="page".
    @prop user An object with `name` and `email`, for the account menu.
    @prop home Where the lockup leads; without one, the `dashboard` route where the imprint has one, and the root otherwise.
    @prop homeLabel The lockup link's accessible name; without one, `foundry::nav.home` with the imprint's name.
    @prop menuLabel The accessible name of the button that opens the panel; without one, `foundry::nav.menu`.
    @slot actions What stands before the account menu from lg up, and closes the panel below it.
    @slot menu The imprint's items in the account menu, each a `flux:menu.item`.

    @example Two links and an action
    @ground bare
    <x-site.app-nav :links="['Agreements' => url()->current(), 'Settings' => '#']" :user="(object) ['name' => 'Ada Visser', 'email' => 'ada@example.com']">
        <x-slot:actions>
            <x-site.button href="#">New agreement</x-site.button>
        </x-slot:actions>
    </x-site.app-nav>
--}}
@php
    $home ??= Route::has('dashboard') ? route('dashboard') : url('/');
    $homeLabel ??= __('foundry::nav.home', ['name' => config('imprint.name')]);
    $menuLabel ??= __('foundry::nav.menu');
    $here = rtrim(url()->current(), '/');

    // Under a root address every page lies, so a root link is current only on the root itself.
    $current = function (string $href) use ($here): bool {
        $address = rtrim(url($href), '/');

        return $address === $here || (trim((string) parse_url($address, PHP_URL_PATH), '/') !== '' && str_starts_with($here, $address.'/'));
    };

    $hasActions = isset($actions) && $actions->isNotEmpty();
    $folds = $links !== [] || $hasActions;
@endphp

<header {{ $attributes->class('border-b border-zinc-200 dark:border-zinc-700') }}>
    <nav aria-label="Main" @if ($folds) x-data="{ open: false }" @keydown.escape.window="open = false" @endif>
        <x-site.container class="flex h-14 items-center gap-8">
            <a href="{{ $home }}" aria-label="{{ $homeLabel }}" class="shrink-0 text-zinc-950 dark:text-zinc-50">
                <x-site.lockup class="h-5" />
            </a>

            <div class="flex items-stretch gap-7 self-stretch max-lg:hidden">
                @foreach ($links as $label => $href)
                    <a href="{{ $href }}" @if ($current($href)) aria-current="page" @endif @class([
                        '-mb-px flex items-center border-b-2 text-copy font-medium whitespace-nowrap',
                        'border-primary-700 dark:border-primary-300 text-zinc-950 dark:text-zinc-50' => $current($href),
                        'border-transparent text-zinc-600 dark:text-zinc-400 hover:text-zinc-950 dark:hover:text-zinc-50' => ! $current($href),
                    ])>{{ $label }}</a>
                @endforeach
            </div>

            <div class="ml-auto flex items-center gap-3">
                @if ($hasActions)
                    <div class="flex items-center gap-3 max-lg:hidden">{{ $actions }}</div>
                @endif

                <x-site.account-menu :user="$user">{{ $menu ?? '' }}</x-site.account-menu>

                @if ($folds)
                    <button type="button" aria-controls="app-menu" :aria-expanded="open" aria-label="{{ $menuLabel }}" @click="open = ! open"
                        class="group relative -mr-2 shrink-0 cursor-pointer rounded-md p-2 text-zinc-950 dark:text-zinc-50 hover:bg-zinc-100 dark:hover:bg-zinc-800 lg:hidden">
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

        @if ($folds)
            <div id="app-menu" x-cloak x-show="open" @click="$event.target.closest('a') && (open = false)"
                x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="-translate-y-2 opacity-0"
                x-transition:leave="transition duration-150 ease-in" x-transition:leave-end="-translate-y-2 opacity-0"
                class="border-t border-zinc-200 dark:border-zinc-700 lg:hidden">
                <x-site.container class="flex flex-col items-start gap-6 py-6">
                    @if ($links !== [])
                        <ul role="list" class="flex w-full flex-col border-l border-zinc-200 dark:border-zinc-700">
                            @foreach ($links as $label => $href)
                                <li>
                                    <a href="{{ $href }}" @if ($current($href)) aria-current="page" @endif @class([
                                        '-ml-px flex border-l-2 py-2.5 pl-4 text-lede font-medium',
                                        'border-primary-700 dark:border-primary-300 text-zinc-950 dark:text-zinc-50' => $current($href),
                                        'border-transparent text-zinc-600 dark:text-zinc-400 hover:text-zinc-950 dark:hover:text-zinc-50' => ! $current($href),
                                    ])>{{ $label }}</a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                    @if ($hasActions)
                        <div class="flex w-full flex-wrap items-center gap-3">{{ $actions }}</div>
                    @endif
                </x-site.container>
            </div>
        @endif
    </nav>
</header>
