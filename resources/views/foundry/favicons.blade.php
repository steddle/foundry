@props(['icons' => null, 'ink' => null])

{{--
    The icons foundry:assets writes, and the browser chrome in the imprint's
    two colours, or another imprint's icons and ink where a page stands for it.

    @group Brand
    @prop icons A base URL whose favicon.svg, favicon-96x96.png and apple-touch-icon.png stand in for the imprint's.
    @prop ink A colour for the browser chrome in both themes, in place of the imprint's paper and ink.

    @example In the head
    @code
    <foundry:favicons />
--}}
@if ($icons)
    <link rel="icon" href="{{ rtrim($icons, '/') }}/favicon.svg" type="image/svg+xml">
    <link rel="icon" href="{{ rtrim($icons, '/') }}/favicon-96x96.png" type="image/png" sizes="96x96">
    <link rel="apple-touch-icon" href="{{ rtrim($icons, '/') }}/apple-touch-icon.png">
@else
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon-adaptive.svg" type="image/svg+xml">
    <link rel="icon" href="/favicon-96x96.png" type="image/png" sizes="96x96">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="manifest" href="/site.webmanifest">
@endif
@if ($ink)
    <meta name="theme-color" content="{{ $ink }}">
@else
    <meta name="theme-color" content="{{ config('imprint.paper') }}" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="{{ config('imprint.ink') }}" media="(prefers-color-scheme: dark)">
@endif
