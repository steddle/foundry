@use('Illuminate\Support\Str')

@props(['title', 'description', 'og' => [], 'flux' => true])

{{--
    A public page of the site: the head, the bar, the page's own content, and
    the footer with the `closing` slot. An imprint hands the bar and the footer
    their links through the `nav` and `footer` slots. No @fluxAppearance: the
    site follows the system theme itself.

    @group Shell
    @prop title The page's title, for its head; what stands before any ` | ` is the og-image's heading where `og` names none.
    @prop description The page's meta description, and the og-image's lede where `og` names none.
    @prop og The og-image's `heading`, `lede`, `eyebrow` and `marked`, or the og-image itself as a slot. Rendered only where `services.ogkit.key` is set.
    @prop flux Loads Flux's scripts and with them Livewire's Alpine; a page that renders no control can turn it off.
    @slot head What the page adds to its head.
    @slot nav The site's bar, where it sets one of its own; without it the foundry's bar without links.
    @slot footer The site's footer, which then holds its own closing section; without it the foundry's footer around the `closing` slot.
    @slot closing The page's closing section, set in the footer on its scene.

    @example A site's layout
    @code
    <foundry:layouts.site :$title :$description :$og>
        <x-slot:nav><foundry:header :links="$links" :home="route('home')" /></x-slot:nav>
        {{ $slot }}
        <x-slot:footer>
            <foundry:footer scene="footer" :links="$links">{{ $closing ?? '' }}</foundry:footer>
        </x-slot:footer>
    </foundry:layouts.site>
--}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <foundry:head :$title :$description />
        {{ $head ?? '' }}
    </head>
    {{-- A short page would otherwise leave the footer halfway up the screen. --}}
    <body class="isolate flex min-h-dvh flex-col">
        @isset($nav)
            {{ $nav }}
        @else
            <foundry:header />
        @endisset

        <main class="flex-1">
            {{ $slot }}
        </main>

        @isset($footer)
            {{ $footer }}
        @else
            <foundry:footer>
                {{ $closing ?? '' }}
            </foundry:footer>
        @endisset

        {{--
            OG Kit fetches this same page and renders whatever sits here into
            its `og:image`, so it stays out of a browser's own render (a
            `<template>` never is) and out of the page's markdown.
        --}}
        @if (config('services.ogkit.key'))
            <template data-og-template data-markdown-skip>
                @if ($og instanceof \Illuminate\View\ComponentSlot)
                    {{ $og }}
                @else
                    <foundry:og-image :heading="$og['heading'] ?? Str::before($title, ' | ')" :lede="$og['lede'] ?? $description" :eyebrow="$og['eyebrow'] ?? null" :marked="$og['marked'] ?? null" />
                @endif
            </template>
        @endif

        @if ($flux)
            @fluxScripts
        @endif
    </body>
</html>
