@use('Illuminate\Support\Str')

@props(['title', 'description', 'og' => [], 'flux' => true])

{{--
    A public page of the site: the head, foundry:bar, the page's own content,
    and the footer with the closing slot. An imprint hands the footer its
    links through the footer slot. No @fluxAppearance: the site follows the
    system theme itself.

    @group Shell
    @prop title The page's title, for its head; what stands before any ` | ` is the og-image's heading where `og` names none.
    @prop description The page's meta description, and the og-image's lede where `og` names none.
    @prop og The og-image's `heading`, `lede`, `eyebrow` and `marked`, or the og-image itself as a slot. Rendered only where `services.ogkit.key` is set.
    @prop flux Loads Flux's scripts and with them Livewire's Alpine; a page that renders no control can turn it off.
    @slot head What the page adds to its head.
    @slot footer The site's footer, which then holds its own closing section; without it the foundry's footer around the `closing` slot.
    @slot closing The page's closing section, set in the footer on its scene.

    @example A site's layout
    @code
    <foundry:layouts.site :$title :$description :$og>
        {{ $slot }}
        <x-slot:footer>
            <foundry:footer scene="footer" :links="$links">{{ $closing ?? '' }}</foundry:footer>
        </x-slot:footer>
    </foundry:layouts.site>
--}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}"@if (config('imprint.palette')) data-palette="{{ config('imprint.palette') }}"@endif>
    <head>
        <foundry:head :$title :$description />
        {{ $head ?? '' }}
    </head>
    {{-- A short page would otherwise leave the footer halfway up the screen. --}}
    <body class="isolate flex min-h-dvh flex-col">
        <foundry:bar />

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
