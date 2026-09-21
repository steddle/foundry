{{--
    The icons foundry:assets writes, and the browser chrome in the imprint's two colours.

    @group Brand

    @example In the head
    @code
    <x-site.favicons />
--}}
<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon-adaptive.svg" type="image/svg+xml">
<link rel="icon" href="/favicon-96x96.png" type="image/png" sizes="96x96">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">
<link rel="manifest" href="/site.webmanifest">
<meta name="theme-color" content="{{ config('imprint.paper') }}" media="(prefers-color-scheme: light)">
<meta name="theme-color" content="{{ config('imprint.ink') }}" media="(prefers-color-scheme: dark)">
