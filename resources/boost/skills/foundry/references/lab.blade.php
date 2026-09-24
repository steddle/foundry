@php
    $facts = \Steddle\Foundry\Boost\Skill::facts();
@endphp
# The lab

Outside production the foundry registers `/labs`, `/design`, `/components` and `/foundry/mail` (`foundry.lab`, `foundry.design`, `foundry.components`, `foundry.mail`), behind `pages.middleware` and, outside local, `pages.guard` from `config/imprint.php`. On those pages the nav is the lab's bar on bone, and elsewhere the links end on Lab. The lab and the design page open on `foundry:sections.page-title`.

## Experiments

`/labs` lists the design page, the components and the experiments `imprint.labs` names, slug => [title, summary], each the imprint's own view `labs.{slug}`: a question still open. An experiment that ships is deleted: its answer lives in the page it shipped to.

{{ $facts['name'] }}'s experiments: {!! $facts['labs'] === [] ? 'none' : collect($facts['labs'])->map(fn ($title, $slug) => '`'.$slug.'` ('.$title.')')->implode(', ') !!}.

## /design

`/design` is the foundry's own page, the same sections in the same order on every imprint: brand, family, colour, type, space, scenes, icons, social images, buttons, forms and feedback. What is the imprint's own comes from `config/imprint.php` under `design`: the accent's name, the rules for the mark, the ramps' names, a sample per type step, the marked phrase, each scene's place and any face of its own under `fonts`, as Blade. What each type step and radius measures is read off the page as it renders, and the badges are the badge's own example. A component of its own belongs on `/components`, never on `/design`.

## /components

`/components` opens on an index of every component the imprint renders, by group, from `Steddle\Foundry\Catalog\Catalog`; a component's page shows each example live with its Blade and a copy button, then its props, with their defaults, and its slots. Where the imprint has components of its own, a toggle shows all, the foundry's or the imprint's, and the lab's Custom components row opens `/components?from=custom`. It marks a foundry component the imprint keeps a copy of, in place of wrapping it, which is the thing to fix. How a component describes itself is in `SKILL.md`.

## /foundry/mail

`/foundry/mail` renders a sample mail through the foundry's theme with the imprint's values, and `/foundry/mail?text` its plain-text part. What sets it is in `mail.md`.
