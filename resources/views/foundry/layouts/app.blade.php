@props(['title', 'description' => null, 'analytics' => null])

{{--
    A signed-in page's document, kept out of search: foundry:bar over the ink
    the page opens on, the page's own content edge to edge, the service line
    under a rule, the toast group and Flux's scripts. Every page opens on an
    foundry:app.band and sets its own container under it, so the bar lies on
    ink here as it does on the site. The toast group's @persist makes Livewire
    inject its assets, Alpine with them, on a page without a component too.

    @group Shell
    @prop title The page's title, followed in `<title>` by the imprint's name.
    @prop description The page's meta description.
    @prop analytics Loads the imprint's analytics on this page, which foundry:head otherwise leaves off every signed-in page; for one whose address carries no token.
    @slot nav A bar in place of foundry:bar, for a page that shows its reader less than the rest: one they still have to act on before anything else.

    @example A settings page
    @code
    <foundry:layouts.app title="Settings">
        <foundry:app.band scene="hero">
            <foundry:page-head title="Settings" lead="Your profile and how you sign in." />
        </foundry:app.band>

        <foundry:container class="py-10">
            …
        </foundry:container>
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
            <foundry:bar />
        @endisset

        <main class="flex-1">
            {{ $slot }}
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
