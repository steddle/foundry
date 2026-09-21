# steddle/foundry

What Steddle's imprints share. steddle is the house; bron and sendnda are
imprints that take its structure in their own colours. Change shared code here,
in `~/Github/steddle/foundry`, never in a site's copy: a site holds only what
is its own.

- **Type**: `resources/css/foundry.css` declares Spectral, Chivo and Chivo Mono,
  the type scale (`display`, `heading-1` to `-3`, `lede`, `copy`, `small`,
  `label`, `meta`, `code`) and the radii, and sources the foundry's views. A
  site's `app.css` imports it after `tailwindcss` and Flux.
- **Colour**: seven ramps on steps 25 to 950 and nothing else; Tailwind's
  palettes are reset. `secondary` (lichen), `info`, `success`, `warning` and
  `danger` live in the foundry; a site's theme adds `zinc`, its grey, and
  `primary`. `300` is the colour and text on ink, `700` text on bone, `50` a
  wash, `800` hover on bone. Flux fills with `primary-300` under `zinc-950`
  text. There are no role tokens: a component writes the pair out,
  `text-zinc-600 dark:text-zinc-400`, and `dark:` fires under
  `prefers-color-scheme` and inside `ink`, so a band on ink reads the same in
  both themes; inside `bone`, a sheet that stays bone in both themes, it never
  fires. An ink ground is `bg-zinc-900 ink` in both themes: `ink` makes the
  element's own `dark:` fire, so a pair on it would always read its dark half.
  Tailwind's colour names resolve to the nearest ramp for Flux's `color`
  props, as an interim until no view passes one.
- **Components**: `x-site.heading` (`size` display, 1, 2, 3 and `tone`) and
  `x-site.text` (`variant` lede, copy, small, label, meta and `tone` body,
  strong, muted, accent, error), both over Flux; `x-site.button`, over `flux:button` with `primary`, `secondary` and `ghost`, `x-site.container`, `x-site.section`,
  `x-site.numbered-section`, `x-site.section-head`, `x-site.breadcrumb`, `x-site.badge`, `x-site.country-select`, `x-site.account-row`, `x-site.checklist`, `x-site.pager`, `x-site.promises` and `x-site.copy-menu`
  resolve from the foundry. The copy menu reads its words from
  `foundry::agents`, in English and Dutch.
- **Header and footer**: `x-foundry::site.nav` takes the links, the home
  address, an `actions` slot and whether a phone folds them into a menu;
  `x-foundry::site.footer` takes the scene, the links or an `items` slot and
  whether the lockup is endorsed, lays one scrim over the scene, rules off a
  closing section set in its slot, and closes on the colophon: the disclaimer
  from `config/imprint.php` and the copyright. A site's own `x-site.nav` and
  `x-site.footer` wrap them with its content.
- **Hero**: `x-site.hero` opens a page on ink under the header: `scene`,
  `align` (`start` or `center`, which chooses the scrim), `tall`, and the
  `eyebrow`, `title` with its `marked` phrase and `lead` it sets itself; the
  slot follows them. `x-site.section` takes `scene` and `align` too, for an
  ink band further down a page. Both draw the scrim `x-site.scene` holds for
  `start` or `center`, measured over every imprint's scenes; the footer keeps
  its own. `x-site.actions` is the row of buttons a band ends on.
  `x-site.ink-page` is a page of its own on ink, an error or a sign-in, with
  `scene` or `card`; it and the footer close on `x-site.service-line`, the
  disclaimer and the copyright, with 'A service by Steddle' where
  `imprint.endorsed` holds, true unless the imprint is Steddle itself.
  `x-site.closing` is the page's last word in the footer's slot: `title`,
  `lead` and `align`, with the actions as its slot. A scene
  is framed once, in `config/imprint.php` under `scenes`, name => its
  object-position classes, and every `x-site.scene` of it reads that; a site's
  stylesheet sources `config/imprint.php` so the classes are built.
- **Script**: a foundry component that holds state uses Alpine, which
  Livewire loads on the page, and `[x-cloak]` holds it back until Alpine has
  read it. A page with such a component loads Livewire's scripts; what only
  appears and disappears is CSS.
- **Docs and legal**: `Route::docs()` and `Route::legal()` register the
  foundry's pages for them, inside `Route::localized()` or on their own. The
  table of contents is `resources/content/[{locale}/]docs.php` and
  `legal.php`, each entry a view under `docs.articles` or `legal.documents`,
  and an entry is published where its view exists. The words around them,
  title, lede, closing, the draft notice and contact, are `imprint.docs` and
  `imprint.legal`; the list on a page is its own `<h2>`s, read by `Outline`.
  Long-form text is the `longform` utility in `foundry.css`.
- **Markdown for agents**: `Steddle\Foundry\Markdown\*` extends
  spatie/laravel-markdown-response. The provider binds the table-aware driver
  and the `/index.md` rewrite; a site's `config/markdown-response.php` names
  the detector and `RemoveMarkdownSkipPreprocessor`. `MarkdownUrl::of()` is
  the one statement of a page's markdown address, `/index.md` for a root.
- **`Steddle\Foundry\Http\Middleware\Noindex`** sets `X-Robots-Tag: noindex`,
  also on the redirect or 403 an `auth` or `can` guard throws.
- **Grain**: the `grain` utility in `foundry.css`, on ink bands only.
- **Published images**: `x-site.og-image`, `x-site.social-preview` and
  `x-site.readme-banner` are rendered from the imprint's
  `config/imprint.php` (`name`, `stylesheet`, and `og` and `banner` copy:
  `heading`, `marked`, `lede`, `eyebrow`, each a sentence or a translation
  key, and an optional `locale` per block) into `public/og-image.png` and
  `public/brand/social/`. `php artisan foundry:assets --url=https://<site>.test`
  renders them through Playwright's own Chromium, never the reader's browser,
  so `playwright` is a devDependency of every imprint. `foundry:assets --check`
  fails where the copy or markup moved since, and every imprint's suite runs
  it. Change the copy in `config/imprint.php`, never the PNG. The copy is
  English whatever languages the site speaks: a Dutch reader reads English, a
  reader in the US does not read Dutch, and a README is English.
- **Icons**: the same command draws the favicons, the touch and manifest
  icons, `site.webmanifest` and `favicon.ico` from `x-site.mark` in the
  imprint's `ink` and `paper`, and the MCP server's two icons where
  `mcp_icons` names them. `x-site.favicons` links them in a head, with the
  browser chrome in those two colours. `x-site.brand-assets` shows either set
  on `/design`, `group="social"` or `group="icon"`.
- **What an imprint supplies**: `x-site.lockup` and `x-site.mark`, and the
  `zinc` and `primary` ramps. A site overrides any
  foundry component by keeping a file of the same name under
  `resources/views/components`, and can wrap the foundry's version inside it
  as `x-foundry::site.<name>` rather than copy it.

- **The lab**: outside production the foundry registers `/labs`, `/design` and
  `/components` (`foundry.lab`, `foundry.design`, `foundry.components`),
  behind `pages.middleware` and, outside local, `pages.guard` from
  `config/imprint.php`. `/labs` lists the design page, the components and
  the experiments `imprint.labs` names, slug => [title, summary], each the
  imprint's own view `labs.{slug}`; `/design` is the foundry's own page, the same sections in the same order on every imprint: brand, family, colour, type, space, scenes, icons, social images, buttons, forms and feedback. What is the imprint's own comes from `config/imprint.php` under `design`: the accent's name, the rules for the mark, the ramps' names, a sample per type step, the marked phrase, each scene's place and any face of its own under `fonts`, as Blade. A component of its own belongs on `/components`, never on `/design`.
  On those pages the nav is the lab's bar on bone, and elsewhere the links
  end on Lab. A page with no hero opens on `x-site.page-title`.
- **`/components`** shows every component an imprint renders, from the
  catalogue in `Steddle\Foundry\Catalog\Catalog`: each example rendered
  live on its ground and printed as Blade. An imprint's own components join
  it on their own: every file under its `resources/views/components/site`
  the foundry neither keeps nor names. The comment a file opens on is its
  description, and each `@example Title` line in it starts a live example,
  its Blade the lines after it; a folder is a group. The toggle on
  `/components` shows all, the foundry's or the imprint's own, and the
  lab's Custom components row opens `/components?from=custom`. It marks a foundry component the
  imprint keeps a copy of, in place of wrapping it, which is the thing to
  fix. Outside production only; `config/imprint.php` names `pages.middleware`
  for it and `pages.guard` for outside local. Add a component to the
  catalogue when it enters the foundry, and every imprint's suite renders it.

## Design language

These rules hold on every page of every imprint, `/design` and `/labs`
included. A site's own guidelines add what its readers need; they never
loosen these.

- **Mono is for code, nothing else.** Chivo Mono sets what a reader could
  paste into a terminal or a config file. Never an eyebrow, a label, a
  caption, a citation, a path, an identifier, a date, a table or a field
  name: those are Chivo, with tabular numerals where they hold figures. Mono
  set after a `font` shorthand takes `slashed-zero tabular-nums`, or its zero
  reads as an O.
- **No middle dot.** A separator is ` | `.
- **No em or en dash in copy.** A colon, a comma or a new sentence.
- **One marker per viewport.** `x-site.marker` lays lichen behind one phrase
  of the hero headline. Never on body copy or anything clickable.
- **No emoji.** The glyphs ✓, ✗ and › are type, and allowed.
- **Sentence case, and no uppercase.** Eyebrows, labels, table heads, badges
  and buttons are set in sentence case, with no tracking. The one uppercase
  line is BY STEDDLE in an imprint's endorsed lockup, which is part of that
  mark.
