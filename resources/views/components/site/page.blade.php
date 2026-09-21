@use('Illuminate\Support\Str')

@props(['title', 'description', 'og' => [], 'flux' => true])

{{--
    A page of the site: the head, the nav, the page's own content, and the
    footer with the `closing` slot. `head` adds to the head. `og` is the
    og-image's props or the og-image itself as a slot. `flux` loads Flux and
    with it Livewire's Alpine; a page that renders no control can leave them
    out. No @fluxAppearance: the site follows the system theme itself.
--}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <x-site.head :$title :$description />
        {{ $head ?? '' }}
    </head>
    {{-- A short page would otherwise leave the footer halfway up the screen. --}}
    <body class="isolate flex min-h-dvh flex-col">
        <x-site.nav />

        <main class="flex-1">
            {{ $slot }}
        </main>

        <x-site.footer>
            {{ $closing ?? '' }}
        </x-site.footer>

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
                    <x-site.og-image :heading="$og['heading'] ?? Str::before($title, ' | ')" :lede="$og['lede'] ?? $description" :eyebrow="$og['eyebrow'] ?? null" :marked="$og['marked'] ?? null" />
                @endif
            </template>
        @endif

        @if ($flux)
            @fluxScripts
        @endif
    </body>
</html>
