@props(['title', 'description' => null, 'analytics' => null, 'footer' => true])

{{--
    A signed-in page's document for an app that navigates by a rail rather
    than by foundry:bar, kept out of search: the rail on the left, the page's
    own content beside it edge to edge, the service line under a rule, the
    toast group and Flux's scripts, as foundry:layouts.app sets them. A page
    sets its own container and opens on an foundry:page-head: an
    foundry:app.band leaves room at its top for a bar this layout has not.

    @group Shell
    @prop title The page's title, followed in `<title>` by the imprint's name.
    @prop description The page's meta description.
    @prop analytics Loads the imprint's analytics on this page, which foundry:head otherwise leaves off every signed-in page; for one whose address carries no token.
    @prop footer Leaves the service line off, for a page that sets it in a column of its own; that page renders foundry:service-line itself.
    @slot sidebar The rail, an foundry:app.sidebar.

    @example An imprint's app layout
    @code
    <foundry:layouts.app.sidebar :$title>
        <x-slot:sidebar>
            <foundry:app.sidebar :user="auth()->user()" :home="route('dashboard')">
                <foundry:app.sidebar.item :href="route('dashboard')" icon="home">Overview</foundry:app.sidebar.item>
                <foundry:app.sidebar.item :href="route('inbox')" icon="inbox" :count="$waiting">Inbox</foundry:app.sidebar.item>
            </foundry:app.sidebar>
        </x-slot:sidebar>

        <foundry:container class="py-10">
            <foundry:page-head :$title />
            {{ $slot }}
        </foundry:container>
    </foundry:layouts.app.sidebar>
--}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <foundry:head :title="$title.' | '.config('imprint.name')" :$description :$analytics />

        <meta name="robots" content="noindex" />
    </head>
    {{-- Flux's stylesheet makes this a grid of its areas, the rail's among them, once a child carries data-flux-main. --}}
    <body class="isolate min-h-dvh bg-zinc-50 dark:bg-zinc-900">
        {{ $sidebar }}

        {{-- Not flux:main, a padded div: the page wants its landmark, and sets its own container. --}}
        <main class="[grid-area:main] min-w-0" data-flux-main>
            {{ $slot }}
        </main>

        @if ($footer)
            <footer class="[grid-area:footer]">
                <foundry:container class="pb-6">
                    <foundry:service-line class="border-t border-zinc-200 dark:border-zinc-700 pt-6" />
                </foundry:container>
            </footer>
        @endif

        <foundry:toasts />

        @fluxScripts
    </body>
</html>
