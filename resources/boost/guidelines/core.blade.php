# steddle/foundry

What Steddle's imprints share. steddle is the house; bron and sendnda are
imprints that take its structure in their own colours. Change shared code here,
in `~/Github/steddle/foundry`, never in a site's copy: a site holds only what
is its own.

- **Type**: `resources/css/foundry.css` declares Spectral, Chivo and Chivo Mono,
  the type scale (`display`, `figure`, `heading-1` to `-3`, `lede`, `copy`,
  `small`, `label`, `meta`, `code`) and the radii, and sources the foundry's views. A
  site's `app.css` imports it after `tailwindcss` and Flux.
- **Colour**: a view names seven ramps on steps 25 to 950 and nothing else;
  Tailwind's palettes are reset. `secondary` (lichen), `info`, `success`,
  `warning` and `danger` live in the foundry; a site's theme adds `zinc`, its
  grey, and `primary`. The foundry also declares `spring` and `wax`, the
  ramps bron's and sendnda's themes point `primary` at, never named in a
  view. `300` is the colour and text on ink, `700` text on bone, `50` a
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
  imprint writes against: `foundry:heading` (`size` display, figure, 1, 2, 3 and
  `tone` strong, accent, inherit) and `foundry:text` (`variant` lede, copy,
  small, label, meta and `tone` body, strong, muted, accent, error,
  inherit), both over Flux; `foundry:button`, over `flux:button` with
  `variant` primary, secondary and ghost, which reads on bone and on ink,
  Flux's `size` and an `icon`, alone for a square button;
  `foundry:container`; `foundry:section` and `foundry:numbered-section`,
  each with `sunken` for a band a step below
  the page; `foundry:tab`, the mark and one line on a plate against an edge,
  square where it meets it, which a page sets `absolute bottom-0` or
  `right-0` as an entry point. Add a component to the catalogue when it
  enters the foundry, and every imprint's suite renders it.
- **What an imprint supplies**: `foundry:lockup`, `foundry:mark` and
  `foundry:bar`, the bar over every page, the signed-in ones included, under
  `resources/views/foundry/`, and the `zinc` and `primary` ramps. A file of
  that name there stands in for the foundry's component, as a published Flux
  component stands in for its own; what the component needs from the site,
  the site hands it where it uses it. A component of the imprint's own lives
  under `resources/views/components/` and is written `x-name`.
- **`/components` and `/design`**: `/components` lists the foundry's
  components and the imprint's own, with a filter to the imprint's own; a
  site-specific device (bron's provision, Send NDA's signature) is a component
  and belongs there. `/design` shows the brand in use (lockup, fonts, ramps,
  scenes, buttons, forms, modals), never a catalogue. Its sections and their
  order are the same on every imprint; what differs per imprint comes from
  `config/imprint.php`, never from a site's own design view.
- **One standard**: before building UI, look in `/components` and in Flux
  Pro; what exists there is extended with a prop or slot in the foundry, never
  drawn a second time in a site. When a foundry component can't do what a page
  needs, it gains a prop or a variant in the foundry and the page uses it; a
  site never hand-rolls the component's markup or classes around it (the `as`
  prop on `foundry:text` is one). A shared component looks the same in every
  imprint: a site passing its own spacing, gradient or classes into one is a
  fault to converge, not a prop to add. What differs per imprint is content
  and the `zinc` and `primary` ramps.
- **Script**: a foundry component that holds state uses Alpine, which
  Livewire loads on the page, and `[x-cloak]` holds it back until Alpine has
  read it. A page with such a component loads Livewire's scripts; what only
  appears and disappears is CSS.
- **Controls**: every control is Flux's own, never the browser's. A date is
  `flux:date-picker` or `flux:calendar`, a time `flux:time-picker`, a choice
  from a list `flux:select variant="listbox"` or `variant="combobox"`, since
  the default `flux:select` renders a native `<select>`; a checkbox, radio or
  switch is `flux:checkbox`, `flux:radio` or `flux:switch`, a text field
  `flux:input` or `flux:textarea`, and a button `foundry:button`.
- **Livewire**: a component is multi-file, a folder with its class beside its
  Blade view, never a single-file component, and no emoji in its name. The
  foundry sets `livewire.make_command` to `mfc` without emoji, so
  `php artisan make:livewire` writes one.
- **The skill**: the `foundry` skill holds the rest, each part in a
  reference of its own: every component the imprint renders with its props,
  slots and an example, the imprint's own among them; composing a page;
  writing a component of the imprint's own; languages; docs and legal;
  the sitemap, llms.txt and markdown for agents; the published images and
  icons; printed documents; the mail theme; sign-in; the Steddle account;
  onboarding; agents over MCP; and the lab. What it says of the imprint it reads at `boost:update`, and the
  imprint's suite fails once that no longer holds. Beside it the foundry
  ships `laravel-code-simplifier`, for a diff before its commit, and
  `sync-docs`, the drift sweep, which reads the imprint's surfaces and
  areas from `.ai/sync-docs.md`.
- **Tests**: `Steddle\Foundry\Testing\Imprint::tests()`, called from the
  imprint's `tests/Feature/FoundryTest.php` with a `viewer` where its lab is
  guarded, is what every imprint runs for what the foundry gives it: the
  catalogue, every framed example, the lab behind its guard and absent in
  production, every public page with its markdown, the error pages, the
  sitemap and the llms files, the published images and the foundry skill. A
  test of foundry behaviour belongs there, never in one imprint's own suite.

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
- **Sign in, never log in.** English copy says sign in and sign out, never
  log in, log out or login as a verb.
- **One marker per viewport.** `foundry:marker` lays lichen behind one phrase
  of the hero headline. Never on body copy or anything clickable.
- **No emoji.** The glyphs ✓, ✗ and › are type, and allowed.
- **Sentence case, and no uppercase.** Eyebrows, labels, table heads, badges
  and buttons are set in sentence case, with no tracking. The one uppercase
  line is BY STEDDLE in an imprint's endorsed lockup, which is part of that
  mark.
