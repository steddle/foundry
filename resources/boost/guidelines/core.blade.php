# steddle/foundry

What Steddle's imprints share. steddle is the house; bron and sendnda are
imprints that take its structure in their own colours. Change shared code here,
in `~/Github/steddle/foundry`, never in a site's copy: a site holds only what
is its own.

- **Type**: `resources/css/foundry.css` declares Spectral, Chivo and Chivo Mono,
  the type scale (`display`, `figure`, `heading-1` to `-3`, `lede`, `copy`,
  `small`, `label`, `meta`, `code`) and the radii, and sources the foundry's views. A
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
- **Components**: `/components` is the full catalogue. The contract every
  imprint writes against: `x-site.heading` (`size` display, figure, 1, 2, 3 and
  `tone` strong, accent, inherit) and `x-site.text` (`variant` lede, copy,
  small, label, meta and `tone` body, strong, muted, accent, error,
  inherit), both over Flux; `x-site.button`, over `flux:button` with
  `primary`, `secondary` and `ghost`; `x-site.container`; `x-site.section`
  and `x-site.numbered-section`, each with `sunken` for a band a step below
  the page. `x-site.copy-menu` reads its words from `foundry::agents`, in
  English and Dutch.
- **Sections**: a page is a run of sections. The foundry holds the base ones
  under `x-site.sections.*`: `hero` and `page-title` to open a page, `split`
  (words beside a `figure` slot), `steps`, `features`, `faq` and `cta`
  between, and `closing` in the footer's slot. Every one takes the same
  words, `eyebrow`, `title` and `lead`, the lede, as props, or `eyebrow` and
  `lead` as slots where they hold markup; its items as a list of pairs or of
  rows keyed the same; the buttons in an `actions` slot, which it sets in
  `x-site.actions`; whatever the default slot holds after the rest; and
  `id`, and `sunken` or `scene` where the band takes one: `closing` takes
  its ground from the footer. Every one sets its words through
  `x-site.section-head`, beside each other or `stacked`, so they read alike
  on every band. A section that is a base one filled with copy is written in
  the page, never kept as a component, and so is a section bound to the
  page's Livewire state, a form. A section with markup of its own is the
  imprint's, under `resources/views/components/site/sections/`, built on a
  base one where its shape allows; a figure a section sets more than once is
  a component of its own beside it. A base section enters the foundry when a
  page uses it.
- **Header and footer**: `x-foundry::site.nav` takes the links, the home
  address, an `actions` slot and whether a phone folds them into a menu;
  `x-foundry::site.footer` takes the scene, the links or an `items` slot and
  whether the lockup is endorsed, lays one scrim over the scene, rules off a
  closing section set in its slot, and closes on the colophon: the disclaimer
  from `config/imprint.php` and the copyright. A site's own `x-site.nav` and
  `x-site.footer` wrap them with its content.
- **Hero**: `x-site.sections.hero` opens a page on ink under the header:
  `scene`, `align` (`start` or `center`, which chooses the scrim), `tall`,
  and a `marked` phrase in its `title`; `actions` and then the slot follow
  the words. `x-site.section` takes `scene` and `align` too, for an
  ink band further down a page. Both draw the scrim `x-site.scene` holds for
  `start` or `center`, measured over every imprint's scenes; the footer keeps
  its own. `x-site.actions` is the row of buttons a band ends on. A scene is
  framed once, in `config/imprint.php` under `scenes`, name => its
  object-position classes, and every `x-site.scene` of it reads that; a
  site's stylesheet sources `config/imprint.php` so the classes are built.
- **Page**: `x-site.page` is every page's document: head, nav, content,
  footer with the `closing` slot, the OG Kit template and Flux unless `flux`
  is false; a site's `layouts/site` wraps it. `x-site.head` writes the meta,
  the canonical address and the alternates, the Open Graph image from OG Kit
  where `services.ogkit.key` is set, `twitter:site` from `imprint.twitter`,
  the icons, `imprint.stylesheet` and `imprint.script`, and in production
  Plausible and Visitors where `imprint.plausible` and `imprint.visitors`
  name them. `x-site.ink-page` is a page of its own on ink, an error or a
  sign-in, with `scene` or `card`; it and the footer close on
  `x-site.service-line`: `imprint.disclaimer`, the copyright, and 'A service
  by Steddle' where `imprint.endorsed` holds, true unless the imprint is
  Steddle itself. `x-site.sections.closing` is the page's last word in the
  footer's slot, centred or, with `align`, at the start.
- **App shell**: `x-site.app-page` is a signed-in page's document, on bone
  and kept out of search: `x-site.app-nav`, the content in a container, the
  service line under a rule and `x-site.toasts`, the toast group persisted
  across `wire:navigate`, which `x-site.ink-page` sets too. `x-site.app-nav`
  is its bar, in flow and ruled off: the lockup, the `links` with the one
  whose address the current one equals or lies under marked, the `actions`
  slot and `x-site.account-menu`, the reader's initials opening their name
  and address, the `menu` slot's items and logging out where `logout` is a
  route. `x-site.page-head` opens the content: a way `back`, the title
  with a `status` beside it, the lede and the page's `actions`.
  `x-site.rows` rules a list of `x-site.record-row`s, each a link over its
  title, status and `meta` with its `actions` outside it; `x-site.empty`
  stands where a list holds nothing yet; `x-site.field-row` sets one
  setting's label and description beside its control, the parent ruling
  the rows.
- **Script**: a foundry component that holds state uses Alpine, which
  Livewire loads on the page, and `[x-cloak]` holds it back until Alpine has
  read it. A page with such a component loads Livewire's scripts; what only
  appears and disappears is CSS.
- **Languages**: `imprint.locales` names each language, `name` and
  `regional` (the Open Graph locale, `nl_NL`); the first answers at the root
  and every other under its own prefix. One language is a site that is not
  multilingual: no prefix, no switch, no alternates.
  `lang/{locale}/routes.php` holds each language's path words, and
  `localized_route()`, `localized_path()`, `localized_url()` and
  `localized_alternates()` read them. `Route::localized()` registers the
  pages its callback names once per language, named `{locale}.{page}`, with
  `SetLocale` as `locale:{locale}` and `FollowVisitorLanguage` on the root
  language's pages; in one language it names the pages as they are. A
  multilingual imprint gets `FollowPreferredLocale` on the `web` group, for a
  page that states no language, the `locale` cookie left plain so a cache in
  front can key on it, `POST locale/{locale}` (`locale.update`) for the
  switch, and an address under the root language's prefix sent 301 to the
  same path without it (`RootLanguagePrefix`).
- **Docs and legal**: `Route::docs()` and `Route::legal()` register the
  foundry's pages for them, inside `Route::localized()` or on their own:
  `docs.index`, `docs.category.{topic}`, `docs.article.{slug}`,
  `legal.index` and `legal.show`. An article is named by its slug alone, so
  two topics holding one slug throw. The table of contents is
  `resources/content/[{locale}/]docs.php` and `legal.php`, each entry a view
  under `docs.articles` or `legal.documents`, and an entry is published
  where its view exists. The words around them are `imprint.docs` and
  `imprint.legal`: `title`, `description`, `lead`, `draft`,
  `closing.title`, `closing.lead`, `closing.action` and `closing.url`, and
  `contact`. Their heroes need the scenes `docs-hero` and `legal-hero`. The
  list on a page is its own `<h2>`s, read by `Outline`. Long-form text is
  the `longform` utility in `foundry.css`, and a link inside running text
  the `link` utility.
- **Pages for search engines and agents**: `imprint.sitemap` names the
  imprint's class implementing `Steddle\Foundry\Contracts\Sitemap`: `pages()`
  answers its public pages in the current locale, `key => [title,
  description, url, render]`, and `sections()` what llms.txt adds. The
  foundry then answers `sitemap.xml`, with alternates where the imprint
  speaks more than one language, `llms.txt` and `llms-full.txt`, from
  `routes/foundry.php`.
- **Markdown for agents**: `Steddle\Foundry\Markdown\*` extends
  spatie/laravel-markdown-response. The provider binds the table-aware driver
  and the `/index.md` rewrite; a site's `config/markdown-response.php` names
  the detector, `DetectsMarkdownRequest`, which never answers a HEAD in
  markdown, and `RemoveMarkdownSkipPreprocessor`. `MarkdownUrl::of()` is
  the one statement of a page's markdown address, `/index.md` for a root.
- **`Steddle\Foundry\Http\Middleware\Noindex`** sets `X-Robots-Tag: noindex`,
  also on the redirect or 403 an `auth` or `can` guard throws.
- **Grain**: the `grain` utility in `foundry.css`, on ink bands only.
- **Printed documents**: `Steddle\Foundry\Pdf\Printer` makes a PDF from a
  page of the imprint's own, through Browsershot, so an imprint that prints
  has `puppeteer` where Browsershot finds it. `capture()` loads the page,
  refuses an error response, and pulls its stylesheets and their fonts into
  the HTML, fetching from the imprint's own host alone; `print()` prints that
  HTML, so a stored copy and its PDF are one render. The page is
  `x-site.print.document` (`title`, `paper` A4 or Letter, `footer`): bone,
  20 mm at the sides, the footer and the page count at the foot of every
  page, no heading left at the foot of a page and no paragraph broken with
  fewer than three lines on either side. It opens on
  `x-site.print.masthead`, ink bled to the paper's edge, and runs as
  `x-site.print.section`s holding `x-site.print.facts`; a section with
  `new-page`, a signature page, starts on a page of its own. The type scale
  is the site's, at a 12.5px rem.
- **Published images**: `x-site.og-image`, `x-site.social-preview` and
  `x-site.readme-banner` are rendered from the imprint's
  `config/imprint.php` (`name`, `stylesheet`, and `og` and `banner` copy:
  `heading`, `marked`, `lede` and, on `og` alone, `eyebrow`, each a
  sentence or a translation key, and an optional `locale` per block) into `public/og-image.png` and
  `public/brand/social/`. `php artisan foundry:assets --url=https://<site>.test`
  renders them through Playwright's own Chromium, never the reader's browser,
  so `playwright` is a devDependency of every imprint. `foundry:assets --check`
  fails where the copy or markup moved since, and every imprint's suite runs
  it. An imprint's README opens on its banner, light and dark, as the
  foundry's does, then its name, one line and a paragraph, and the
  sections What this codebase does, Local development and Documentation. Change the copy in `config/imprint.php`, never the PNG. The copy is
  English whatever languages the site speaks: a Dutch reader reads English, a
  reader in the US does not read Dutch, and a README is English.
- **Icons**: the same command draws the favicons, the touch and manifest
  icons, `site.webmanifest` and `favicon.ico` from `x-site.mark` in the
  imprint's `ink` and `paper`, and the MCP server's two icons where
  `mcp_icons` names them. `x-site.favicons` links them in a head, with the
  browser chrome in those two colours. `x-site.brand-assets` shows either set
  on `/design`, `group="social"` or `group="icon"`.
- **What an imprint supplies**: `x-site.lockup` and `x-site.mark`, and the
  `zinc` and `primary` ramps. A site overrides any foundry component by
  keeping a file of the same name under `resources/views/components`, and
  can wrap the foundry's version inside it as `x-foundry::site.<name>`
  rather than copy it.
- **The lab**: outside production the foundry registers `/labs`, `/design`
  and `/components` (`foundry.lab`, `foundry.design`,
  `foundry.components`), behind `pages.middleware` and, outside local,
  `pages.guard` from `config/imprint.php`. `/labs` lists the design page,
  the components and the experiments `imprint.labs` names, slug => [title,
  summary], each the imprint's own view `labs.{slug}`: a question still
  open. An experiment that ships is deleted: its answer lives in the page
  it shipped to. `/design` is the
  foundry's own page, the same sections in the same order on every imprint:
  brand, family, colour, type, space, scenes, icons, social images,
  buttons, forms and feedback. What is the imprint's own comes from
  `config/imprint.php` under `design`: the accent's name, the rules for the
  mark, the ramps' names, a sample per type step, the marked phrase, each
  scene's place and any face of its own under `fonts`, as Blade. What each
  type step and radius measures is read off the page as it renders, and
  the badges are the badge's own example. A component of its own belongs
  on `/components`, never on `/design`. On
  those pages the nav is the lab's bar on bone, and elsewhere the links end
  on Lab. The lab and the design page open on `x-site.sections.page-title`.
- **`/components`** opens on an index of every component an imprint
  renders, by group, from the catalogue in
  `Steddle\Foundry\Catalog\Catalog`; a component's page shows each example
  live with its Blade and a copy button, then its props, with their
  defaults, and its slots. A component, the foundry's or the imprint's,
  describes itself in the comment its file opens on: the prose is its
  description, `@group` its place in the index, `@prop name …` and
  `@slot name …` one line each, and each `@example Title` a live example,
  its Blade the lines after it, optionally led by `@ground page|ink|bare` or
  `@code`. A `bare` example is a band or a page: it renders on a page of its
  own, framed at a desktop's or a phone's width. The groups are Shell,
  Sections, Layout, Navigation, Type, Elements, Forms, Brand and Print. An
  imprint's own components join the index on their own: every file under
  its `resources/views/components/site` the foundry neither keeps nor
  names. Under that directory a folder with an `index` is one component and
  its other files are its parts; a file in a folder named after a group,
  `sections/`, is in that group, and every other names its group with
  `@group`, or the catalogue throws. Where the imprint has components of
  its own, a toggle on `/components` shows all, the foundry's or the
  imprint's, and the lab's Custom components row opens
  `/components?from=custom`. `/components` marks a foundry component the
  imprint keeps a copy of, in place of wrapping it, which is the thing to
  fix. Add a component to the catalogue when it enters the foundry, and
  every imprint's suite renders it.

- **Tests**: `Steddle\Foundry\Testing\Imprint::tests()`, called from the
  imprint's `tests/Feature/FoundryTest.php` with a `viewer` where its lab is
  guarded, is what every imprint runs for what the foundry gives it: the
  catalogue, every framed example, the lab behind its guard and absent in
  production, every public page with its markdown, the sitemap and the llms
  files, and the published images. A test of foundry behaviour belongs
  there, never in one imprint's own suite.

## Design language

These rules hold on every page of every imprint, `/design` and `/labs`
included. A site's own guidelines add what its readers need; they never
loosen these.

- **Mono is for code, nothing else.** Chivo Mono sets what a reader could
  paste into a terminal or a config file: a terminal panel, its prompt and
  its output alike, an install field, and a command in running text. Never
  an eyebrow, a label, a caption, a citation, a path, an identifier, a date,
  a table or a field name: those are Chivo, with tabular numerals where they
  hold figures. Mono set after a `font` shorthand takes `slashed-zero
  tabular-nums`, or its zero reads as an O.
- **No middle dot.** A separator is ` | `.
- **No em or en dash in copy.** A colon, a comma or a new sentence.
- **One marker per viewport.** `x-site.marker` lays lichen behind one phrase
  of the hero headline. Never on body copy or anything clickable.
- **No emoji.** The glyphs ✓, ✗ and › are type, and allowed.
- **Sentence case, and no uppercase.** Eyebrows, labels, table heads, badges
  and buttons are set in sentence case, with no tracking. The one uppercase
  line is BY STEDDLE in an imprint's endorsed lockup, which is part of that
  mark.
