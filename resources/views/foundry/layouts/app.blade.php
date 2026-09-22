@props(['title', 'description' => null, 'links' => [], 'user', 'home' => null, 'flush' => false, 'analytics' => null])

{{--
    A signed-in page's document, on bone and kept out of search: foundry:app.header,
    the page's own content in a container, the service line under a rule, the
    toast group and Flux's scripts. The toast group's @persist makes Livewire
    inject its assets, Alpine with them, on a page without a component too.

    @group Shell
    @prop title The page's title, followed in `<title>` by the imprint's name.
    @prop description The page's meta description.
    @prop links label => href for foundry:app.header, which marks the one whose address the current one equals or lies under.
    @prop user An object with `name` and `email`, for the account menu.
    @prop home Where the lockup leads, as foundry:app.header takes it.
    @prop flush Sets the slot edge to edge, for a page that opens on an foundry:app.band and sets its own container under it, and lays the bar over the band.
    @prop analytics Loads the imprint's analytics on this page, which foundry:head otherwise leaves off every signed-in page; for one whose address carries no token.
    @slot actions What stands in the bar before the account menu.
    @slot menu The imprint's items in the account menu, each a `flux:menu.item`.
    @slot nav A bar of the imprint's own in place of foundry:app.header, for an imprint whose site and app share one; links, user, home, actions and menu then go unused.

    @example A dashboard's layout
    @code
    <foundry:layouts.app :$title :links="['Agreements' => route('dashboard'), 'Settings' => route('settings')]" :user="auth()->user()">
        <x-slot:actions>
            <foundry:button :href="route('ndas.create')">New agreement</foundry:button>
        </x-slot:actions>
        <x-slot:menu>
            <flux:menu.item :href="route('settings')" icon="cog-6-tooth">Settings</flux:menu.item>
        </x-slot:menu>

        {{ $slot }}
    </foundry:layouts.app>
--}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <foundry:head :title="$title.' | '.config('imprint.name')" :$description :$analytics />

        <meta name="robots" content="noindex" />
    </head>
    {{-- A short page would otherwise leave the service line halfway up the screen. --}}
    <body class="isolate flex min-h-dvh flex-col bg-zinc-50 dark:bg-zinc-900">
        @isset($nav)
            {{ $nav }}
        @else
            <foundry:app.header :$links :$user :$home :overlay="$flush">
                <x-slot:actions>{{ $actions ?? '' }}</x-slot:actions>
                <x-slot:menu>{{ $menu ?? '' }}</x-slot:menu>
            </foundry:app.header>
        @endisset

        <main class="flex-1">
            @if ($flush)
                {{ $slot }}
            @else
                <foundry:container class="py-10">
                    {{ $slot }}
                </foundry:container>
            @endif
        </main>

        <footer>
            <foundry:container class="pb-6">
                <foundry:service-line class="border-t border-zinc-200 dark:border-zinc-700 pt-6" />
            </foundry:container>
        </footer>

        <foundry:toasts />

        @fluxScripts
    </body>
</html>
