@php
    $facts = \Steddle\Foundry\Boost\Skill::facts();
    $copy = fn (array $block): string => collect($block)->map(fn ($value, $key) => '- `'.$key.'`: '.$value)->implode("\n");
@endphp
# Published images and icons

## The images

`foundry:og-image`, `foundry:social-preview` and `foundry:readme-banner` are rendered from the imprint's `config/imprint.php` (`name`, `stylesheet`, and `og` and `banner` copy: `heading`, `marked`, `lede` and, on `og` alone, `eyebrow`, each a sentence or a translation key, and an optional `locale` per block) into `public/og-image.png` and `public/brand/social/`.

Change the copy in `config/imprint.php`, never the PNG. The copy is English whatever languages the site speaks: a Dutch reader reads English, a reader in the US does not read Dutch, and a README is English.

{{ $facts['name'] }}'s `og`:

{!! $facts['og'] === [] ? '- none' : $copy($facts['og']) !!}

Its `banner`:

{!! $facts['banner'] === [] ? '- none' : $copy($facts['banner']) !!}

## The command

`php artisan foundry:assets --url=https://<site>.test` renders them through Playwright's own Chromium, never the reader's browser, so `playwright` is a devDependency of every imprint. `foundry:assets --check` fails where the copy or markup moved since, and every imprint's suite runs it: a change to the copy, or to the markup of these components in the foundry, is followed by the command.

## Icons

The same command draws the favicons, the touch and manifest icons, `site.webmanifest` and `favicon.ico` from `foundry:mark` in the imprint's `ink` and `paper`, and the MCP server's two icons where `mcp_icons` names them. `foundry:favicons` links them in a head, with the browser chrome in those two colours. `foundry:brand-assets` shows either set on `/design`, `group="social"` or `group="icon"`.

## Mail

It renders the images a mail draws, since mail clients draw no SVG and load no web fonts: `public/brand/mail/logo-2x.png`, the imprint's lockup on the mail's paper, and for an endorsed imprint `public/brand/mail/steddle-logo-2x.png`, Steddle's wordmark. What the mail does with them is in `mail.md`. No published file has an `@` in its name: Laravel Cloud's edge answers 404 to one a mail client asks for unencoded.

## The README

An imprint's README opens on its banner, light and dark, as the foundry's does, then its name, one line and a paragraph, and the sections What this codebase does, Local development and Documentation.
