@php
    $facts = \Steddle\Foundry\Boost\Skill::facts();
@endphp
# Search engines and agents

## The sitemap and the llms files

`imprint.sitemap` names the imprint's class implementing `Steddle\Foundry\Contracts\Sitemap`{!! $facts['sitemap'] ? ', in '.$facts['name'].' `'.$facts['sitemap'].'`' : '; '.$facts['name'].' names none' !!}. `pages()` answers its public pages in the current locale, `key => [title, description, url, render]`, and `sections()` what `llms.txt` adds. The foundry then answers `sitemap.xml`, with alternates where the imprint speaks more than one language, `llms.txt` and `llms-full.txt`, from `routes/foundry.php`.

A public page enters the sitemap by entering `pages()`. The site's suite renders every one of them, checks it carries no `noindex`, answers markdown and stands in `sitemap.xml`.

## Markdown for agents

`Steddle\Foundry\Markdown\*` extends spatie/laravel-markdown-response. The provider binds the table-aware driver and the `/index.md` rewrite; a site's `config/markdown-response.php` names the detector, `DetectsMarkdownRequest`, which never answers a HEAD in markdown, and `RemoveMarkdownSkipPreprocessor`. `MarkdownUrl::of()` is the one statement of a page's markdown address, `/index.md` for a root. `x-site.copy-menu` offers a page's markdown to a reader, with its words from `foundry::agents`, in English and Dutch.

## Out of search

`Steddle\Foundry\Http\Middleware\Noindex` sets `X-Robots-Tag: noindex`, also on the redirect or 403 an `auth` or `can` guard throws. `x-site.app-page` and the lab are kept out of search.

## The head

`x-site.head` writes the meta, the canonical address and the alternates, the Open Graph image from OG Kit where `services.ogkit.key` is set, `twitter:site` from `imprint.twitter`, the icons, `imprint.stylesheet` and `imprint.script`, and in production Plausible and Visitors where `imprint.plausible` and `imprint.visitors` name them.
