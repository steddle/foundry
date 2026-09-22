@props(['title', 'description', 'scene' => null, 'card' => false, 'home' => null])

{{--
    A page for one task, without the site's navigation: signing in, an error,
    a consent. The lockup and the language switch, the slot, and the service
    line, kept out of search. Its ground is ink.

    @group Shell
    @prop title The page's title, followed in `<title>` by the imprint's name.
    @prop description The page's meta description.
    @prop scene A photo under one even scrim, measured on bron's well: the heading at 10.6:1 and the disclaimer over the brightest corner at 4.6:1. Without one the page is grain and the mark ghosted in its corner.
    @prop card Sets the slot on a card in the middle, its title at heading-1's phone size, with `ink` on the bands around it rather than the body, so Flux's dark variant keeps out of the card. A card page shows no language switch.
    @prop home Where the lockup leads; without one, the `home` route in the current language where the imprint speaks more than one, and the root otherwise.

    @example An error page
    @code
    <foundry:layouts.focus title="Page not found" description="Nothing answers at this address." scene="error">
        <foundry:text variant="label" tone="accent">Error 404</foundry:text>
        <foundry:heading size="1" level="1">Page not found</foundry:heading>
    </foundry:layouts.focus>
--}}
@php
    $home ??= \Steddle\Foundry\Locales::multilingual() ? localized_route('home') : url('/');
@endphp

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <foundry:head :title="$title.' | '.config('imprint.name')" :$description />

        <meta name="robots" content="noindex" />
    </head>
    <body @class(['relative isolate flex min-h-dvh flex-col overflow-hidden bg-zinc-900', 'ink' => ! $card, 'grain' => ! $scene])>
        @if ($scene)
            <foundry:scene :name="$scene" eager scrim="bg-zinc-900/82" />
        @else
            <foundry:mark class="pointer-events-none absolute -right-16 -bottom-20 -z-10 size-[28rem] text-zinc-50 opacity-[0.05] max-sm:hidden" />
        @endif

        <header class="ink">
            <foundry:container class="flex items-center justify-between gap-6 py-5">
                <a href="{{ $home }}" aria-label="{{ __('foundry::nav.home', ['name' => config('imprint.name')]) }}" class="shrink-0 text-zinc-50">
                    <foundry:lockup class="h-5" />
                </a>
                {{-- Not on a card: a sign-in or a consent has no counterpart, and switching would drop the request it holds. --}}
                @if (\Steddle\Foundry\Locales::multilingual() && ! $card)
                    <foundry:locale-switch :aria-label="__('foundry::nav.language')" />
                @endif
            </foundry:container>
        </header>

        @if ($card)
            <main class="flex flex-1 items-center justify-center px-4 py-12">
                {{-- The title holds heading-1's phone size: at its desktop size a card this narrow breaks it over three lines. --}}
                <div class="flex w-full max-w-md flex-col gap-6 rounded-lg bg-zinc-25 dark:bg-zinc-800 p-6 sm:p-9 [&_h1]:text-3xl!">
                    {{ $slot }}
                </div>
            </main>
        @else
            <main class="flex flex-1 items-center">
                <foundry:container class="flex flex-col items-start gap-6 py-16">
                    {{ $slot }}
                </foundry:container>
            </main>
        @endif

        <footer class="ink">
            <foundry:container class="pb-6">
                <foundry:service-line class="border-t border-zinc-50/13 pt-6" />
            </foundry:container>
        </footer>

        <foundry:toasts />

        @fluxScripts
    </body>
</html>
