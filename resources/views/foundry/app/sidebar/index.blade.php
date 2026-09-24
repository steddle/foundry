@props(['home' => '/', 'homeLabel' => null, 'user' => null, 'navigate' => true])

{{--
    The rail a signed-in app navigates by, over flux:sidebar, on ink in both
    themes: the lockup, a search trigger, the links and their groups, and the
    reader's account menu at its foot. Its links follow with wire:navigate,
    and it opens at the offset the reader last left it at, across pages and
    reloads in the tab. Below lg it is a drawer, opened from a
    bar across the top that holds the lockup and the account menu. An imprint
    sets it in the `sidebar` slot of foundry:layouts.app.sidebar. Its parts:
    foundry:app.sidebar.item, a link with an icon and a count, the current one
    on a lichen rule and marked aria-current="page";
    foundry:app.sidebar.group, links that fold under a heading, and with
    `remember` open as the reader last left them; and
    foundry:app.sidebar.search, the button that opens the imprint's search.

    @group Shell
    @prop home Where the lockup leads.
    @prop homeLabel The lockup link's accessible name; without one, `foundry::nav.home` with the imprint's name.
    @prop navigate Follows the lockup's link home with Livewire's wire:navigate, as each foundry:app.sidebar.item does its own; false for a full page load.
    @prop user An object with `name` and `email`, as foundry:account-menu takes it, whose menu then sits at the rail's foot, and in the bar below lg.
    @slot search An foundry:app.sidebar.search above the links.
    @slot slot The links: foundry:app.sidebar.item, and foundry:app.sidebar.group around the ones that fold.
    @slot footer Links set apart at the foot of the rail, over the account menu, for tools such as the design lab: foundry:app.sidebar.item, in a nav of their own, named `foundry::nav.tools` unless the slot gives an `aria-label`.
    @slot account The imprint's items in the account menu, each a `flux:menu.item`. The rail's foot and the bar each render them, so an item holds no id and no Livewire component.

    @example A founders' dashboard
    @ground bare
    <div class="min-h-[36rem] bg-zinc-50 dark:bg-zinc-900">
        <foundry:app.sidebar :user="(object) ['name' => 'Ada Visser', 'email' => 'ada@example.com']">
            <x-slot:search>
                <foundry:app.sidebar.search kbd="⌘K" />
            </x-slot:search>

            <foundry:app.sidebar.item href="#" icon="home" current>Overview</foundry:app.sidebar.item>
            <foundry:app.sidebar.item href="#" icon="inbox" count="12">Inbox</foundry:app.sidebar.item>
            <foundry:app.sidebar.item href="#" icon="building-office" count="4">Companies</foundry:app.sidebar.item>

            <foundry:app.sidebar.group heading="Board" remember="board" open>
                <foundry:app.sidebar.item href="#">Q3 update</foundry:app.sidebar.item>
                <foundry:app.sidebar.item href="#">Minutes, 12 Sep</foundry:app.sidebar.item>
            </foundry:app.sidebar.group>

            <foundry:app.sidebar.group heading="Legal" remember="legal" :expanded="false">
                <foundry:app.sidebar.item href="#">Shareholders' agreement</foundry:app.sidebar.item>
            </foundry:app.sidebar.group>

            <x-slot:footer>
                <foundry:app.sidebar.item href="#" icon="swatch">Design lab</foundry:app.sidebar.item>
            </x-slot:footer>

            <x-slot:account>
                <flux:menu.item href="#" icon="cog-6-tooth">Settings</flux:menu.item>
            </x-slot:account>
        </foundry:app.sidebar>

        <main class="[grid-area:main]" data-flux-main>
            <foundry:container class="py-10">
                <foundry:page-head eyebrow="Overview" title="Good morning, Ada." lead="Four companies, twelve items waiting." />
            </foundry:container>
        </main>
    </div>
--}}
@php
    $homeLabel ??= __('foundry::nav.home', ['name' => config('imprint.name')]);
@endphp

{{-- Livewire's own scroll keeping takes the rail back to where it was on a page the reader returns to through history. --}}
<flux:sidebar sticky collapsible="mobile" {{ $attributes->merge(['wire:navigate:scroll' => true, 'data-scroll-id' => 'foundry-rail'])->class('border-e border-zinc-50/13 bg-zinc-900 ink') }}>
    <flux:sidebar.header>
        <a href="{{ $home }}" aria-label="{{ $homeLabel }}" class="px-2 text-zinc-50" @if ($navigate) wire:navigate @endif>
            <foundry:lockup class="h-5" />
        </a>
        <flux:sidebar.toggle icon="x-mark" :aria-label="__('foundry::nav.close')" class="lg:hidden" />
    </flux:sidebar.header>

    {{ $search ?? '' }}

    <flux:sidebar.nav :aria-label="__('foundry::nav.main')">
        {{ $slot }}
    </flux:sidebar.nav>

    <flux:sidebar.spacer />

    @isset($footer)
        <flux:sidebar.nav :attributes="$footer->attributes->merge(['aria-label' => __('foundry::nav.tools')])->class('border-t border-zinc-50/13 pt-4')">
            {{ $footer }}
        </flux:sidebar.nav>
    @endisset

    @if ($user)
        <foundry:account-menu :user="$user" position="top" align="start" class="max-lg:hidden">
            <x-slot:trigger>
                <flux:sidebar.profile :name="$user->name" :initials="$user->initials ?? null" :aria-label="__('foundry::nav.account', ['name' => $user->name])" />
            </x-slot:trigger>
            {{ $account ?? '' }}
        </foundry:account-menu>
    @endif

    {{--
        Livewire keeps an element's offset only across history: a rail rendered anew on a page
        reached by a link, or on a reload, would open at the top. So it keeps its offset in the
        tab's session storage too, and takes it back here, its last child, before it paints.
        Livewire runs the script again on each wire:navigate; on a page from history it has set
        data-scroll-y, which comes first, so the two agree from the first frame.
    --}}
    <script>
        (() => {
            const rail = document.currentScript.parentElement;

            try {
                rail.scrollTop = Number(rail.dataset.scrollY ?? sessionStorage.getItem('foundry-sidebar-scroll')) || 0;
            } catch {}

            rail.addEventListener('scroll', () => {
                try {
                    sessionStorage.setItem('foundry-sidebar-scroll', rail.scrollTop);
                } catch {}
            }, { passive: true });
        })();
    </script>
</flux:sidebar>

<flux:header class="bg-zinc-900 ink lg:hidden">
    <flux:sidebar.toggle icon="bars-2" inset="left" :aria-label="__('foundry::nav.menu')" />

    <a href="{{ $home }}" aria-label="{{ $homeLabel }}" class="ms-2 text-zinc-50" @if ($navigate) wire:navigate @endif>
        <foundry:lockup class="h-5" />
    </a>

    <flux:spacer />

    @if ($user)
        <foundry:account-menu :user="$user">{{ $account ?? '' }}</foundry:account-menu>
    @endif
</flux:header>
