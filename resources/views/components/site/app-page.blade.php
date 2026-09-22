@props(['title', 'description' => null, 'links' => [], 'user', 'home' => null])

{{--
    A signed-in page's document, on bone and kept out of search: x-site.app-nav,
    the page's own content in a container, the service line under a rule, the
    toast group and Flux's scripts.

    @group Shell
    @prop title The page's title, followed in `<title>` by the imprint's name.
    @prop description The page's meta description.
    @prop links label => href for x-site.app-nav, which marks the one whose address the current one equals or lies under.
    @prop user An object with `name` and `email`, for the account menu.
    @prop home Where the lockup leads, as x-site.app-nav takes it.
    @slot actions What stands in the bar before the account menu.
    @slot menu The imprint's items in the account menu, each a `flux:menu.item`.

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
        <x-site.head :title="$title.' | '.config('imprint.name')" :$description />

        <meta name="robots" content="noindex" />
    </head>
    {{-- A short page would otherwise leave the service line halfway up the screen. --}}
    <body class="isolate flex min-h-dvh flex-col bg-zinc-50 dark:bg-zinc-900">
        <x-site.app-nav :$links :$user :$home>
            <x-slot:actions>{{ $actions ?? '' }}</x-slot:actions>
            <x-slot:menu>{{ $menu ?? '' }}</x-slot:menu>
        </x-site.app-nav>

        <main class="flex-1">
            <x-site.container class="py-10">
                {{ $slot }}
            </x-site.container>
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
