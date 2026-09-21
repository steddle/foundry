# steddle/foundry

What Steddle's imprints share. steddle is the house; bron and sendnda are
imprints that take its structure in their own colours. Change shared code here,
in `~/Github/steddle/foundry`, never in a site's copy: a site holds only what
is its own.

- **Type**: `resources/css/foundry.css` declares Spectral, Chivo and Chivo Mono
  and sources the foundry's views. A site's `app.css` imports it after
  `tailwindcss`.
- **Components**: `x-site.container`, `x-site.section` and
  `x-site.numbered-section` resolve from the foundry.
- **Markdown for agents**: `Steddle\Foundry\Markdown\*` extends
  spatie/laravel-markdown-response. The provider binds the table-aware driver
  and the `/index.md` rewrite; a site's `config/markdown-response.php` names
  the detector and `RemoveMarkdownSkipPreprocessor`.
- **`Steddle\Foundry\Http\Middleware\Noindex`** sets `X-Robots-Tag: noindex`,
  also on the redirect or 403 an `auth` or `can` guard throws.
- **Grain**: the `grain` utility in `foundry.css`, on ink bands only.
- **Published images**: `x-site.og-image`, `x-site.social-preview` and
  `x-site.readme-banner` are rendered from the imprint's
  `config/imprint.php` (`name`, `stylesheet`, and `og` and `banner` copy:
  `heading`, `marked`, `lede`, `eyebrow`) into `public/og-image.png` and
  `public/brand/social/`. `php artisan foundry:assets --url=https://<site>.test`
  renders them through headless Chrome; `foundry:assets --check` fails where
  the copy or markup moved since, and every imprint's suite runs it. Change the
  copy in `config/imprint.php`, never the PNG.
- **What an imprint supplies**: `x-site.lockup`, `x-site.mark` and
  `x-site.marker`, and the tokens `bg-page`, `bg-inverse`, `text-strong`,
  `text-body`, `text-accent` and the `ink` variant. A site overrides any
  foundry component by keeping a file of the same name under
  `resources/views/components`, and can wrap the foundry's version inside it
  as `x-foundry::site.<name>` rather than copy it.

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
