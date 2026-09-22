@php
    $facts = \Steddle\Foundry\Boost\Skill::facts();
    $locales = $facts['locales'];
@endphp
# Languages

@if (count($locales) === 1)
{{ $facts['name'] }} speaks one language, `{{ $locales[0] }}`: no prefix, no switch, no alternates, and `Route::localized()` names the pages as they are.
@else
{{ $facts['name'] }} speaks {!! \Steddle\Foundry\Boost\Skill::codes($locales) !!}. `{{ $locales[0] }}` answers at the root and every other under its own prefix; a page's route is named `{locale}.{page}`, `{{ $locales[0] }}.home`.
@endif

`imprint.locales` names each language, `name` and `regional` (the Open Graph locale, `nl_NL`); the first answers at the root and every other under its own prefix. One language is a site that is not multilingual: no prefix, no switch, no alternates.

`lang/{locale}/routes.php` holds each language's path words, and `localized_route()`, `localized_path()`, `localized_url()` and `localized_alternates()` read them. Translated paths differ, so a page finds its counterpart by route name, never by its path.

`Route::localized()` registers the pages its callback names once per language, named `{locale}.{page}`, with `SetLocale` as `locale:{locale}` and `FollowVisitorLanguage` on the root language's pages; in one language it names the pages as they are.

A multilingual imprint gets:

- `FollowPreferredLocale` on the `web` group, for a page that states no language;
- the `locale` cookie left plain, so a cache in front can key on it;
- `POST locale/{locale}` (`locale.update`) for the switch, `x-site.locale-switch`;
- an address under the root language's prefix sent 301 to the same path without it (`RootLanguagePrefix`).

The published images and the README are English whatever languages the site speaks; see `published-images.md`.
