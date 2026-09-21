<?php echo '<?xml version="1.0" encoding="UTF-8"?>'."\n"; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
    @foreach ($urls as $url)
        <url>
            <loc>{{ $url['loc'] }}</loc>
            @foreach ($url['alternates'] as $locale => $href)
                <xhtml:link rel="alternate" hreflang="{{ $locale }}" href="{{ $href }}" />
            @endforeach
            @if ($url['alternates'] !== [])
                <xhtml:link rel="alternate" hreflang="x-default" href="{{ $url['alternates'][\Steddle\Foundry\Locales::root()] }}" />
            @endif
        </url>
    @endforeach
</urlset>
