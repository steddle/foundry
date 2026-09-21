@use('Spatie\MarkdownResponse\Middleware\ProvideMarkdownResponse')
@use('Steddle\Foundry\Locales')
@use('Steddle\Foundry\Markdown\MarkdownUrl')

@props(['title', 'description'])

{{--
    The page's head: its meta, its canonical address and, where the imprint
    speaks more than one language, its counterpart in each; its Open Graph
    image, from OG Kit where a key is set; the icons; the imprint's
    stylesheet and script; and Plausible and Visitors in production where the
    imprint names them. The slot adds what is the page's own.

    @group Bands

    @example In a page's head
    @code
    <x-site.head :$title :$description />
--}}
@php($canonical = request()->fullUrlWithoutQuery(['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'gclid', 'fbclid', 'ref']))
{{-- A page kept out of search, a sign-in or a signing link, names no address of its own: its query can hold a token. --}}
@php($indexed = request()->route() && ! in_array(\Steddle\Foundry\Http\Middleware\Noindex::class, request()->route()->gatherMiddleware(), true))
@php($ogImage = config('services.ogkit.key') && $indexed ? 'https://ogkit.dev/img/'.config('services.ogkit.key').'.jpeg?url='.urlencode($canonical) : asset('og-image.png'))

<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="description" content="{{ $description }}" />
<meta name="color-scheme" content="light dark" />

{{-- Kept out of the markdown, which opens on the page's own heading: the title repeats it with the site's name. --}}
<title data-markdown-skip>{{ $title }}</title>

{{-- Only on a page search may read: an error nobody routed and a page kept out of search are no address to point a search engine at. --}}
@if ($indexed)
    <link rel="canonical" href="{{ $canonical }}" />
@endif
@if (Locales::multilingual() && Locales::counterpart(request(), Locales::root()) !== null)
    @foreach (localized_alternates() as $locale => $href)
        <link rel="alternate" hreflang="{{ $locale }}" href="{{ $href }}" />
        @if ($locale === Locales::root())
            <link rel="alternate" hreflang="x-default" href="{{ $href }}" />
        @endif
    @endforeach
@endif
@if (in_array(ProvideMarkdownResponse::class, request()->route()?->gatherMiddleware() ?? [], true))
    <link rel="alternate" type="text/markdown" href="{{ MarkdownUrl::of(request()->url()) }}" />
@endif

<meta property="og:site_name" content="{{ config('imprint.name') }}" />
<meta property="og:type" content="website" />
<meta property="og:title" content="{{ $title }}" />
<meta property="og:description" content="{{ $description }}" />
@if ($indexed)
    <meta property="og:url" content="{{ $canonical }}" />
@endif
<meta property="og:locale" content="{{ Locales::regional(app()->getLocale()) }}" />
@foreach (array_diff(Locales::all(), [app()->getLocale()]) as $locale)
    <meta property="og:locale:alternate" content="{{ Locales::regional($locale) }}" />
@endforeach
<meta property="og:image" content="{{ $ogImage }}" />
<meta property="og:image:width" content="1200" />
<meta property="og:image:height" content="630" />
<meta property="og:image:alt" content="{{ $title }}" />
<meta name="twitter:card" content="summary_large_image" />
@if (config('imprint.twitter'))
    <meta name="twitter:site" content="{{ config('imprint.twitter') }}" />
@endif
<meta name="twitter:image" content="{{ $ogImage }}" />

<x-site.favicons />

@vite(array_filter([config('imprint.stylesheet'), config('imprint.script')]))

{{ $slot }}

@production
    @if (config('imprint.plausible'))
        <script defer data-domain="{{ config('imprint.plausible') }}" src="https://plausible.io/js/plausible.js"></script>
    @endif
    @if (config('imprint.visitors'))
        <script src="https://cdn.visitors.now/v.js" data-token="{{ config('imprint.visitors') }}"></script>

        {{-- An event the page handed on through the session for this one request. --}}
        @session('visitors')
            <script>window.visitors?.track(@js($value['name']), @js($value['props']))</script>
        @endsession
    @endif
@endproduction
