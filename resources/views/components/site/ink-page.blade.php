@props(['title', 'description', 'scene' => null, 'card' => false, 'home' => null])

{{--
    A page of its own on ink, for an error or for signing in: the lockup and
    the language switch, the slot, and the service line, kept out of search.
    `scene` lays a photo under one even scrim, measured on bron's well: the
    heading at 10.6:1 and the disclaimer over the brightest corner at 4.6:1;
    without one the page is grain and the mark ghosted in its corner. `card`
    sets the slot on a card in the middle, with `ink` on the bands around it
    rather than the body, so Flux's dark variant keeps out of the card.

    @group Bands

    @example An error page
    @code
    <x-site.ink-page title="Page not found" description="Nothing answers at this address." scene="error">
        <x-site.text variant="label" tone="accent">Error 404</x-site.text>
        <x-site.heading size="1" level="1">Page not found</x-site.heading>
    </x-site.ink-page>
--}}
@php
    $home ??= \Steddle\Foundry\Locales::multilingual() ? localized_route('home') : url('/');
@endphp

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <x-site.head :title="$title.' | '.config('imprint.name')" :$description />

        <meta name="robots" content="noindex" />
    </head>
    <body @class(['relative isolate flex min-h-dvh flex-col overflow-hidden bg-zinc-900', 'ink' => ! $card, 'grain' => ! $scene])>
        @if ($scene)
            <x-site.scene :name="$scene" eager scrim="bg-zinc-900/82" />
        @else
            <x-site.mark class="pointer-events-none absolute -right-16 -bottom-20 -z-10 size-[28rem] text-zinc-50 opacity-[0.05] max-sm:hidden" />
        @endif

        <header class="ink">
            <x-site.container class="flex items-center justify-between gap-6 py-5">
                <a href="{{ $home }}" aria-label="{{ __('foundry::nav.home', ['name' => config('imprint.name')]) }}" class="shrink-0 text-zinc-50">
                    <x-site.lockup class="h-6" />
                </a>
                {{-- Not on a card: a sign-in or a consent has no counterpart, and switching would drop the request it holds. --}}
                @if (\Steddle\Foundry\Locales::multilingual() && ! $card)
                    <x-site.locale-switch :aria-label="__('foundry::nav.language')" />
                @endif
            </x-site.container>
        </header>

        @if ($card)
            <main class="flex flex-1 items-center justify-center px-4 py-12">
                <div class="flex w-full max-w-md flex-col gap-6 rounded-lg bg-zinc-25 dark:bg-zinc-800 p-6 sm:p-9">
                    {{ $slot }}
                </div>
            </main>
        @else
            <main class="flex flex-1 items-center">
                <x-site.container class="flex flex-col items-start gap-6 py-16">
                    {{ $slot }}
                </x-site.container>
            </main>
        @endif

        <footer class="ink">
            <x-site.container class="pb-6">
                <x-site.service-line class="border-t border-zinc-50/13 pt-6" />
            </x-site.container>
        </footer>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
