@php
    $facts = \Steddle\Foundry\Boost\Skill::facts();
    $copy = fn (array $block): string => collect($block)->map(fn ($value, $key) => '- `'.$key.'`: '.$value)->implode("\n");
@endphp
# Published images and icons

## The images

`x-site.og-image`, `x-site.social-preview` and `x-site.readme-banner` are rendered from the imprint's `config/imprint.php` (`name`, `stylesheet`, and `og` and `banner` copy: `heading`, `marked`, `lede` and, on `og` alone, `eyebrow`, each a sentence or a translation key, and an optional `locale` per block) into `public/og-image.png` and `public/brand/social/`.

Change the copy in `config/imprint.php`, never the PNG. The copy is English whatever languages the site speaks: a Dutch reader reads English, a reader in the US does not read Dutch, and a README is English.

{{ $facts['name'] }}'s `og`:

{!! $facts['og'] === [] ? '- none' : $copy($facts['og']) !!}

Its `banner`:

{!! $facts['banner'] === [] ? '- none' : $copy($facts['banner']) !!}

## The command

`php artisan foundry:assets --url=https://<site>.test` renders them through Playwright's own Chromium, never the reader's browser, so `playwright` is a devDependency of every imprint. `foundry:assets --check` fails where the copy or markup moved since, and every imprint's suite runs it: a change to the copy, or to the markup of these components in the foundry, is followed by the command.

## Icons

The same command draws the favicons, the touch and manifest icons, `site.webmanifest` and `favicon.ico` from `x-site.mark` in the imprint's `ink` and `paper`, and the MCP server's two icons where `mcp_icons` names them. `x-site.favicons` links them in a head, with the browser chrome in those two colours. `x-site.brand-assets` shows either set on `/design`, `group="social"` or `group="icon"`.

## The README

An imprint's README opens on its banner, light and dark, as the foundry's does, then its name, one line and a paragraph, and the sections What this codebase does, Local development and Documentation.
