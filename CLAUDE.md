# steddle/foundry

The package every Steddle site is built on. A change here reaches every imprint, so it is tested in every imprint before it is pushed, and carried into each one after.

The rules the imprints follow are the foundry's guideline, the same text Laravel Boost renders into each site's `CLAUDE.md`:

@resources/boost/guidelines/core.blade.php

## The imprints

| Imprint | Path | Remote | Languages | Lab |
|---|---|---|---|---|
| steddle | `~/Sites/steddle` | `mischasigtermans/steddle` | en | open; absent in production |
| bron | `~/Sites/bron` | `steddle/bron` | nl at the root, en | operators only, open in local; absent in production |
| sendnda | `~/Sites/sendnda` | `sendnda/app` | en | signed in, open in local; absent in production |
| righted | `~/Sites/righted` | none yet | en | open; absent in production |
| fly | `~/Sites/fly` | `steddle/fly` | en | signed in everywhere; `/design` in production |
| account | `~/Sites/account` | `steddle/account` | en, nl | open; absent in production |

The lab is `/labs`, `/design`, `/components` and `/foundry/mail`, set by `pages` in each `config/imprint.php`: `pages.middleware` holds everywhere, `pages.guard` everywhere but local, and production serves only what `pages.production` names, and only behind a guard.

`bin/imprints` holds the same list, with each suite's command; a new imprint goes into both. bron's suite runs on a database pair of its own, `bron_test_foundry` and `bron_corpus_test_foundry`, under `TEST_TOKEN=foundry`, so it never meets another session's run.

## Carrying a change through

1. `vendor/bin/pest --compact` and `vendor/bin/pint` on the files you changed here.
2. `bin/imprints check`: sync, test, then reset, holding a lock per imprint that a second run from any session waits on. Every imprint runs this checkout from its `vendor/`, with the guideline and the `foundry` skill installed from it, then goes back to its locked foundry, so its tracked `CLAUDE.md` and skill are left as they were. `sync` and `test` run the halves alone to look into a failure; `reset` puts an imprint back after.
3. Commit and push the foundry once every suite passes.
4. `bin/imprints update` (`composer update steddle/foundry` and `boost:install`), then `bin/imprints test` again.
5. Commit each imprint by pathspec: its `composer.lock`, its `CLAUDE.md`, its skills directories, and what the change touched in it. Push an imprint only on Mischa's word.

A visual change is looked at with `bin/shoot <imprint> <path>... [--dark] [--phone] [--as=<email>]`: it builds the imprint, renders each page at full length in Playwright's Chromium and prints the PNG paths; `--as` signs in through the foundry's `foundry.shoot` route, signed and registered only where `APP_ENV` is local, whether the imprint signs in by link or through the Steddle account.

A change to the copy or markup of the published images needs `php artisan foundry:assets --url=https://<imprint>.test` in each imprint, or its suite fails the asset check.

## Working in an imprint from here

- Read the imprint's own `CLAUDE.md`, and `.ai/rules` where it has them, before editing it: they do not load in a session that starts here.
- Other sessions work in the same trees. `bin/imprints status` shows what is open; commit with `git commit -- <paths>`, never `git add -A`.
- What an imprint shares with the others belongs here, not in its copy. A site keeps its content, its `config/imprint.php`, its `foundry:lockup`, `foundry:mark`, `foundry:bar` and palette, its words for the error pages, and components with markup of its own.

## Tests

`Steddle\Foundry\Testing\Imprint::tests()` is the suite every imprint runs for what the foundry gives it: the catalogue, every framed example, the lab behind its guard and absent in production, every public page with its markdown, the error pages, the sitemap and the llms files, the published images, and the `foundry` skill's fingerprint. Each imprint registers it in `tests/Feature/FoundryTest.php`, with a `viewer` where its lab is guarded. A test of foundry behaviour goes there, never into one imprint's suite. The foundry's own suite (`tests/`) runs on Testbench with `workbench/` as its imprint.

The suite and `vendor/bin/testbench serve` read the workbench's build through a `public/build` link inside Testbench. `composer update`, and `testbench serve` or `tinker` on exit, drop that link, and the suite then fails on 'Vite manifest not found' while served pages lose their CSS. `vendor/bin/testbench workbench:sync` puts it back; composer runs it after every autoload dump.

## Laravel conventions

From the Laravel Boost guidelines the imprints carry, what holds here too:

- PHP 8.3 and up: constructor property promotion, explicit return and parameter types, curly braces on every control structure, PHPDoc array shapes over inline comments.
- Pest for tests, Pint for style.
- Flux for every control. `foundry:button`, `foundry:badge`, `foundry:heading` and `foundry:text` wrap Flux, and a site writes against them.
- Tailwind 4: `@theme` tokens and `@utility` in `resources/css/foundry.css`, no config file.
- Livewire 4 and its Alpine for state; what only appears and disappears is CSS.
- Boost's MCP tools answer from an application, so for documentation search, database queries or logs, use them from an imprint's session.

## Commits

The foundry is committed and pushed once every imprint's suite passes. An imprint is committed by pathspec and pushed on Mischa's word.
