@php
    $facts = \Steddle\Foundry\Boost\Skill::facts();
@endphp
# Docs and legal pages

`Route::docs()` and `Route::legal()` register the foundry's pages for them, inside `Route::localized()` or on their own: `docs.index`, `docs.category.{topic}`, `docs.article.{slug}`, `legal.index` and `legal.show`. An article is named by its slug alone, so two topics holding one slug throw.

The table of contents is `resources/content/[{locale}/]docs.php` and `legal.php`, each entry a view under `docs.articles` or `legal.documents`, `{base}.{locale}.{slug}` or `{base}.{slug}`, and an entry is published where its view exists: a page listed before it is written is no link to a 404.

The words around them are `imprint.docs` and `imprint.legal`: `title`, `description`, `lead`, `draft`, `closing.title`, `closing.lead`, `closing.action` and `closing.url`, and `contact`. Their heroes need the scenes `docs-hero` and `legal-hero`. The list on a page is its own `<h2>`s, read by `Outline`. Long-form text is the `longform` utility, a link inside it the `link` utility.

## In {{ $facts['name'] }}, in {{ $facts['locales'][0] }}

@if ($facts['docs'] === null)
- Docs: not routed.
@else
@foreach ($facts['docs'] as $topic => $articles)
- Docs topic `{{ $topic }}`: {!! \Steddle\Foundry\Boost\Skill::codes($articles) !!}
@endforeach
@endif
@if ($facts['legal'] === null)
- Legal: not routed.
@else
@foreach ($facts['legal'] as $audience => $documents)
- Legal for `{{ $audience }}`: {!! \Steddle\Foundry\Boost\Skill::codes($documents) !!}
@endforeach
@endif
