@props(['title', 'description', 'scene' => null, 'card' => false, 'home' => null, 'client' => null])

{{--
    A page for signing in, a second step or a consent, without the site's
    navigation, kept out of search. Its ground is ink; foundry:layouts.error
    sets an error page on it too. Without a card: the lockup and the language
    switch over the slot, and the service line under it. With one: the lockup
    over the card, the two together in the middle, and on one line at the
    foot Terms and Privacy where the imprint publishes them, and steddle.com.

    @group Shell
    @prop title The page's title, followed in `<title>` by the imprint's name.
    @prop description The page's meta description.
    @prop scene A photo under one even scrim, measured on bron's well: the heading at 10.6:1 and the disclaimer over the brightest corner at 4.6:1. Without one the page is grain and the mark ghosted in its corner.
    @prop card Sets the slot on a card under the lockup, its title at heading-1's phone size, with `ink` on the lockup and the foot rather than the body, so Flux's dark variant keeps out of the card. A card page shows no language switch and no service line.
    @slot footer In card mode, a band a step below the card at its foot, full-bleed with the card's padding: the way on from a consent, its Continue and Cancel.
    @slot after In card mode, a line under the card, centred in its column, small and muted on the ground.
    @prop client The product a sign-in or a consent is for, as `imprint.auth.client` answers it, which it falls back to: `name`, `palette`, `lockup`, `icons` and `ink`. The page takes its palette, its lockup over the card, its icons and browser chrome, and its name in `<title>`; each that is null leaves the imprint's own.
    @prop home Where the lockup leads; without one, the `home` route in the current language where the imprint speaks more than one, and the root otherwise.

    @example A sign-in
    @code
    <foundry:layouts.auth title="Sign in" description="Sign in with a link by email." card>
        <foundry:heading size="1" level="1">Sign in</foundry:heading>
        <foundry:text tone="muted">We'll email you a link. No password needed.</foundry:text>
    </foundry:layouts.auth>

    @example A consent with its way on at the foot, and a line under the card
    @code
    <foundry:layouts.auth title="Continue to Send NDA" description="Continue to Send NDA with your Steddle account." card>
        <foundry:heading size="1" level="1">Continue to Send NDA</foundry:heading>
        <foundry:text tone="muted">Send NDA gets your name and email address.</foundry:text>
        <x-slot:footer>
            <foundry:text variant="small" tone="muted">You'll continue to sendnda.com.</foundry:text>
            <foundry:button class="w-full">Continue</foundry:button>
            <foundry:button variant="ghost" class="w-full">Cancel</foundry:button>
        </x-slot:footer>
        <x-slot:after>You can leave Send NDA at any time in your Steddle account.</x-slot:after>
    </foundry:layouts.auth>
--}}
@php
    $home ??= \Steddle\Foundry\Locales::multilingual() ? localized_route('home') : url('/');
    $client ??= \Steddle\Foundry\Auth\LoginLink::clientFor(request());
    $palette = $client['palette'] ?? config('imprint.palette');

    if ($card) {
        $published = Route::has(\Steddle\Foundry\Locales::multilingual() ? app()->getLocale().'.legal.show' : 'legal.show')
            ? collect(\Steddle\Foundry\Content::legal()['audiences'])->flatMap(fn (array $audience): array => array_keys($audience['documents']))->all()
            : [];

        $foot = collect(['terms', 'privacy'])
            ->filter(fn (string $slug): bool => in_array($slug, $published, true))
            ->mapWithKeys(fn (string $slug): array => [__("foundry::footer.{$slug}") => localized_route('legal.show', $slug)])
            ->put('steddle.com', 'https://steddle.com');
    }
@endphp

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}"@if ($palette) data-palette="{{ $palette }}"@endif>
    <head>
        <foundry:head :title="$title.' | '.($client['name'] ?? config('imprint.name'))" :$description :icons="$client['icons'] ?? null" :ink="$client['ink'] ?? null" />

        <meta name="robots" content="noindex" />
    </head>
    <body @class(['relative isolate flex min-h-dvh flex-col overflow-hidden bg-zinc-900', 'ink' => ! $card, 'grain' => ! $scene])>
        @if ($scene)
            <foundry:scene :name="$scene" eager scrim="bg-zinc-900/82" />
        @else
            {{-- Clipped here: body's overflow passes to the viewport, and would leave the mark sticking out past the page. --}}
            <div class="pointer-events-none absolute inset-0 -z-10 overflow-hidden max-sm:hidden" aria-hidden="true">
                @if ($client['icons'] ?? null)
                    {{-- The product's own mark, as foundry:assets publishes it; one that fails to load leaves the corner bare. --}}
                    <img src="{{ rtrim($client['icons'], '/') }}/brand/logos/mark-paper.svg" alt="" class="absolute -right-16 -bottom-20 size-[28rem] opacity-[0.05]" onerror="this.remove()">
                @else
                    <foundry:mark class="absolute -right-16 -bottom-20 size-[28rem] text-zinc-50 opacity-[0.05]" />
                @endif
            </div>
        @endif

        @if ($card)
            <main class="flex flex-1 flex-col items-center justify-center gap-8 px-4 py-12">
                {{-- No language switch: a sign-in or a consent has no counterpart, and switching would drop the request it holds. --}}
                <a href="{{ $home }}" aria-label="{{ __('foundry::nav.home', ['name' => config('imprint.name')]) }}" class="ink shrink-0 text-zinc-50">
                    @if ($client['lockup'] ?? null)
                        <img src="{{ $client['lockup'] }}" alt="{{ $client['name'] }}" class="h-8 w-auto sm:h-10">
                    @else
                        <foundry:lockup class="h-8 sm:h-10" />
                    @endif
                </a>

                {{-- The title holds heading-1's phone size: at its desktop size a card this narrow breaks it over three lines. --}}
                <div class="flex w-full max-w-md flex-col rounded-lg bg-zinc-25 dark:bg-zinc-800 [&_h1]:text-3xl!">
                    <div class="flex flex-col gap-6 p-6 sm:p-9">
                        {{ $slot }}
                    </div>

                    @if (isset($footer) && $footer->isNotEmpty())
                        <div class="flex flex-col gap-3 rounded-b-lg border-t border-zinc-200 dark:border-zinc-700 bg-zinc-100 dark:bg-zinc-900/50 px-6 py-5 sm:px-9 sm:py-6">
                            {{ $footer }}
                        </div>
                    @endif
                </div>

                @if (isset($after) && $after->isNotEmpty())
                    <foundry:text variant="small" tone="muted" class="ink w-full max-w-md text-center">{{ $after }}</foundry:text>
                @endif
            </main>

            <footer class="ink px-4 pb-3">
                <foundry:text as="nav" variant="small" tone="muted" class="flex flex-wrap items-center justify-center gap-x-2">
                    @foreach ($foot as $label => $href)
                        @unless ($loop->first)
                            <span aria-hidden="true">|</span>
                        @endunless
                        {{-- The padding makes a tap target of a line of small type. --}}
                        <a href="{{ $href }}" class="py-3 hover:text-zinc-950 dark:hover:text-zinc-50">{{ $label }}</a>
                    @endforeach
                </foundry:text>
            </footer>
        @else
            <header class="ink">
                <foundry:container class="flex items-center justify-between gap-6 py-5">
                    <a href="{{ $home }}" aria-label="{{ __('foundry::nav.home', ['name' => config('imprint.name')]) }}" class="shrink-0 text-zinc-50">
                        <foundry:lockup class="h-5" />
                    </a>
                    @if (\Steddle\Foundry\Locales::multilingual())
                        <foundry:locale-switch :aria-label="__('foundry::nav.language')" />
                    @endif
                </foundry:container>
            </header>

            <main class="flex flex-1 items-center">
                <foundry:container class="flex flex-col items-start gap-6 py-16">
                    {{ $slot }}
                </foundry:container>
            </main>

            <footer class="ink">
                <foundry:container class="pb-6">
                    <foundry:service-line class="border-t border-zinc-50/13 pt-6" />
                </foundry:container>
            </footer>
        @endif

        <foundry:toasts />

        @fluxScripts
    </body>
</html>
