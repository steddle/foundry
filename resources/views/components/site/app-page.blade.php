@props(['title', 'description' => null, 'links' => [], 'user', 'home' => null, 'flush' => false, 'analytics' => null])

{{--
    A signed-in page's document, on bone and kept out of search: x-site.app-nav,
    the page's own content in a container, the service line under a rule, the
    toast group and Flux's scripts. The toast group's @persist makes Livewire
    inject its assets, Alpine with them, on a page without a component too.

    @group Shell
    @prop title The page's title, followed in `<title>` by the imprint's name.
    @prop description The page's meta description.
    @prop links label => href for x-site.app-nav, which marks the one whose address the current one equals or lies under.
    @prop user An object with `name` and `email`, for the account menu.
    @prop home Where the lockup leads, as x-site.app-nav takes it.
    @prop flush Sets the slot edge to edge, for a page that opens on an x-site.app-band and sets its own container under it, and lays the bar over the band.
    @prop analytics Loads the imprint's analytics on this page, which x-site.head otherwise leaves off every signed-in page; for one whose address carries no token.
    @slot actions What stands in the bar before the account menu.
    @slot menu The imprint's items in the account menu, each a `flux:menu.item`.
    @slot nav A bar of the imprint's own in place of x-site.app-nav, for an imprint whose site and app share one; links, user, home, actions and menu then go unused.

    @example A dashboard's layout
    @code
    <x-site.app-page :$title :links="['Agreements' => route('dashboard'), 'Settings' => route('settings')]" :user="auth()->user()">
        <x-slot:actions>
            <x-site.button :href="route('ndas.create')">New agreement</x-site.button>
        </x-slot:actions>
        <x-slot:menu>
            <flux:menu.item :href="route('settings')" icon="cog-6-tooth">Settings</flux:menu.item>
        </x-slot:menu>

        {{ $slot }}
    </x-site.app-page>
--}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <x-site.head :title="$title.' | '.config('imprint.name')" :$description :$analytics />

        <meta name="robots" content="noindex" />
    </head>
    {{-- A short page would otherwise leave the service line halfway up the screen. --}}
    <body class="isolate flex min-h-dvh flex-col bg-zinc-50 dark:bg-zinc-900">
        @isset($nav)
            {{ $nav }}
        @else
            <x-site.app-nav :$links :$user :$home :overlay="$flush">
                <x-slot:actions>{{ $actions ?? '' }}</x-slot:actions>
                <x-slot:menu>{{ $menu ?? '' }}</x-slot:menu>
            </x-site.app-nav>
        @endisset

        <main class="flex-1">
            @if ($flush)
                {{ $slot }}
            @else
                <x-site.container class="py-10">
                    {{ $slot }}
                </x-site.container>
            @endif
        </main>

        <footer>
            <x-site.container class="pb-6">
                <x-site.service-line class="border-t border-zinc-200 dark:border-zinc-700 pt-6" />
            </x-site.container>
        </footer>

        <x-site.toasts />

        @fluxScripts
    </body>
</html>
