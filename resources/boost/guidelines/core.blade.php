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
  the page. Add a component to the catalogue when it enters the foundry, and
  every imprint's suite renders it.
- **What an imprint supplies**: `x-site.lockup` and `x-site.mark`, and the
  `zinc` and `primary` ramps. A site overrides any foundry component by
  keeping a file of the same name under `resources/views/components`, and
  can wrap the foundry's version inside it as `x-foundry::site.<name>`
  rather than copy it.
- **Script**: a foundry component that holds state uses Alpine, which
  Livewire loads on the page, and `[x-cloak]` holds it back until Alpine has
  read it. A page with such a component loads Livewire's scripts; what only
  appears and disappears is CSS.
- **The skill**: the `foundry` skill holds the rest, each part in a
  reference of its own: every component the imprint renders with its props,
  slots and an example, the imprint's own among them; composing a page;
  writing a component of the imprint's own; languages; docs and legal;
  the sitemap, llms.txt and markdown for agents; the published images and
  icons; printed documents; and the lab. What it says of the imprint it
  reads at `boost:update`, and the imprint's suite fails once that no
  longer holds.
- **Tests**: `Steddle\Foundry\Testing\Imprint::tests()`, called from the
  imprint's `tests/Feature/FoundryTest.php` with a `viewer` where its lab is
  guarded, is what every imprint runs for what the foundry gives it: the
  catalogue, every framed example, the lab behind its guard and absent in
  production, every public page with its markdown, the sitemap and the llms
  files, the published images and the foundry skill. A test of foundry behaviour belongs
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
