---
name: foundry
description: "Steddle's foundry as this imprint uses it: every component it renders, the foundry's `foundry:*` and its own `x-name`, with props, slots and an example; composing a page from sections; writing a component of the imprint's own; languages and localized routes; docs and legal pages; sitemap.xml, llms.txt and the markdown answer for agents; the OG image, social preview, README banner and icons from foundry:assets; printed PDFs; the mail theme; sign-in; the Steddle account; onboarding; agents over MCP and deleting an account; and the lab at /labs, /design, /components and /foundry/mail. Use for any page, component, route, copy or asset work in the site."
---
@php
    $facts = \Steddle\Foundry\Boost\Skill::facts();
    $name = $facts['name'];
    $components = collect($facts['components']);
@endphp
# Foundry in {{ $name }}

{{ $name }} is built on steddle/foundry. Shared code changes in `~/Github/steddle/foundry`, never in a site's copy; a site holds only what is its own. The guideline in `CLAUDE.md` holds what applies to every task: type, colour, the component contract and the design language. This skill holds the rest, and what it says of {{ $name }} it read from the site at the last `php artisan boost:update`.

## References

Open the one the task needs.

| Reference | For |
|---|---|
| `references/pages.md` | Composing a page: sections, the hero and scenes, header and footer, the page document, the app shell |
| `references/components/{group}.md` | Every component in one group, with its props, slots and an example: {{ collect(\Steddle\Foundry\Catalog\Catalog::groups())->keys()->map(fn ($group) => strtolower($group))->implode(', ') }} |
| `references/languages.md` | `imprint.locales`, localized routes and their helpers, the language switch |
| `references/docs-and-legal.md` | The docs and legal pages and their tables of contents |
| `references/search-and-agents.md` | `sitemap.xml`, `llms.txt`, the markdown answer, `noindex`, the head's meta |
| `references/published-images.md` | The OG image, social preview, README banner, favicons and MCP icons, `foundry:assets` |
| `references/print.md` | A PDF from a page of the site's own |
| `references/mail.md` | The mail theme: `imprint.mail`, its images, the greeting, per-mailable options, `/foundry/mail` |
| `references/sign-in.md` | `imprint.auth`: a link by email and a passkey, remembered, on Fortify's login route |
| `references/account.md` | `imprint.account`: signing in through the Steddle account, its events, the `staff` gate and the privacy part |
| `references/onboarding.md` | `imprint.onboarding`: an account names itself on /welcome before any signed-in page |
| `references/mcp.md` | `imprint.mcp`: the consent screen, OAuth hardening, connecting and disconnecting agents, deleting an account |
| `references/lab.md` | `/labs`, `/design`, `/components` and `/foundry/mail`, and an experiment's life |

## {{ $name }} at a glance

- Languages: {{ count($facts['locales']) === 1 ? $facts['locales'][0].', one language: no prefix, no switch, no alternates' : implode(', ', $facts['locales']).', '.$facts['locales'][0].' at the root' }}
- Sitemap: {!! $facts['sitemap'] ? '`'.$facts['sitemap'].'`' : 'none, so no sitemap.xml, llms.txt or markdown answer' !!}
- Scenes: {!! $facts['scenes'] === [] ? 'none' : \Steddle\Foundry\Boost\Skill::codes($facts['scenes']) !!}
- Docs: {{ $facts['docs'] === null ? 'not routed' : Str::plural('topic', count($facts['docs']), prependCount: true).', '.Str::plural('article', collect($facts['docs'])->flatten()->count(), prependCount: true).' published' }}
- Legal: {{ $facts['legal'] === null ? 'not routed' : Str::plural('document', collect($facts['legal'])->flatten()->count(), prependCount: true).' published' }}
- Experiments in the lab: {!! $facts['labs'] === [] ? 'none' : collect($facts['labs'])->map(fn ($title, $slug) => '`'.$slug.'` ('.$title.')')->implode(', ') !!}

## Components

Every component {{ $name }} renders, by group. Marked are {{ $name }}'s own, and a foundry component it stands in for with a file of its own, the thing to fix. The group's reference holds the props, slots and an example.
@foreach ($components->groupBy('group') as $group => $entries)

### {{ $group }}

@foreach ($entries as $component)
- `{{ $component['tag'] }}`{!! match (true) { $component['from'] === 'custom' => ' (own)', $component['held'] => ' (✗ stood in for)', default => '' } !!}: {{ \Steddle\Foundry\Boost\Skill::summary($component['description']) }}
@endforeach
@endforeach

## A component of {{ $name }}'s own

- It lives under `resources/views/components` and is written `x-name`, Laravel's own tag; the foundry's components are `foundry:name`. A Livewire component lives there too, its view beside its class, and stays out of the catalogue. It joins `/components` and this skill on its own: every file there. A folder with an `index` is one component and its other files are its parts; a file in a folder named after a group, `sections/`, is in that group.
- It describes itself in the comment its file opens on, after its `{{ '@' }}props`: the prose is its description, `{{ '@' }}group` its place in the index, one of {{ implode(', ', array_keys(\Steddle\Foundry\Catalog\Catalog::groups())) }}, or the catalogue throws. `{{ '@' }}prop name …` and `{{ '@' }}slot name …` take a line each, and each `{{ '@' }}example Title` is a live example, its Blade the lines after it, optionally led by `{{ '@' }}ground page|ink|bare` or `{{ '@' }}code`. A `bare` example is a band or a page, framed at a desktop's and a phone's width.
- It writes against `foundry:heading`, `foundry:text`, `foundry:button` and Flux, never raw type or colour.
- A section with markup of its own is the site's, under `sections/`, built on a base section where its shape allows. A base section filled with copy is written in the page, never kept as a component, and so is a section bound to the page's Livewire state.
- To stand in for a foundry component, keep a file of the same name under `resources/views/foundry`, as a published Flux component stands in for its own. What the foundry's component needs from the site, the site hands it where it uses it.
- What another imprint could use belongs in the foundry, not here.
- Every example renders in the site's suite; after adding one, run `php artisan boost:update` so this skill names it.

<!-- foundry-skill {{ \Steddle\Foundry\Boost\Skill::fingerprint() }} -->
