# steddle/foundry

The package every Steddle site is built on. A change here reaches every imprint, so it is tested in every imprint before it is pushed, and carried into each one after.

The rules the imprints follow are the foundry's guideline, the same text Laravel Boost renders into each site's `CLAUDE.md`:

@resources/boost/guidelines/core.blade.php

## The imprints

| Imprint | Path | Remote | Languages | Lab |
|---|---|---|---|---|
| steddle | `~/Sites/steddle` | `mischasigtermans/steddle` | en | open |
| bron | `~/Sites/bron` | `steddle/bron` | nl at the root, en | operators only |
| sendnda | `~/Sites/sendnda` | `sendnda/app` | en | signed in |
| righted | `~/Sites/righted` | none yet | en | open |

`bin/imprints` holds the same list, with each suite's command; a new imprint goes into both. bron's suite runs on a database pair of its own, `bron_test_foundry` and `bron_corpus_test_foundry`, under `TEST_TOKEN=foundry`, so it never meets another session's run.

## Carrying a change through

1. `vendor/bin/pest --compact` and `vendor/bin/pint --dirty` here.
2. `bin/imprints sync`, then `bin/imprints test`: every imprint runs this checkout from its `vendor/`, with the guideline and the `foundry` skill installed from it.
3. Commit and push the foundry once every suite passes.
4. `bin/imprints update` (`composer update steddle/foundry` and `boost:install`), then `bin/imprints test` again.
5. Commit each imprint by pathspec: its `composer.lock`, its `CLAUDE.md` and `AGENTS.md`, its skills directories, and what the change touched in it. Push an imprint only on Mischa's word.

A change to the copy or markup of the published images needs `php artisan foundry:assets --url=https://<imprint>.test` in each imprint, or its suite fails the asset check.

## Working in an imprint from here

- Read the imprint's own `CLAUDE.md`, and `.ai/rules` where it has them, before editing it: they do not load in a session that starts here.
- Other sessions work in the same trees. `bin/imprints status` shows what is open; commit with `git commit -- <paths>`, never `git add -A`.
- What an imprint shares with the others belongs here, not in its copy. A site keeps its content, its `config/imprint.php`, its `x-site.lockup`, `x-site.mark` and ramps, and components with markup of its own.

## Tests

`Steddle\Foundry\Testing\Imprint::tests()` is the suite every imprint runs for what the foundry gives it: the catalogue, every framed example, the lab behind its guard and absent in production, every public page with its markdown, the sitemap and the llms files, the published images, and the `foundry` skill's fingerprint. Each imprint registers it in `tests/Feature/FoundryTest.php`, with a `viewer` where its lab is guarded. A test of foundry behaviour goes there, never into one imprint's suite. The foundry's own suite (`tests/`) runs on Testbench with `workbench/` as its imprint.

## Laravel conventions

From the Laravel Boost guidelines the imprints carry, what holds here too:

- PHP 8.3 and up: constructor property promotion, explicit return and parameter types, curly braces on every control structure, PHPDoc array shapes over inline comments.
- Pest for tests, Pint for style.
- Flux for every control. `x-site.button`, `x-site.badge`, `x-site.heading` and `x-site.text` wrap Flux, and a site writes against them.
- Tailwind 4: `@theme` tokens and `@utility` in `resources/css/foundry.css`, no config file.
- Livewire 4 and its Alpine for state; what only appears and disappears is CSS.
- Boost's MCP tools answer from an application, so for documentation search, database queries or logs, use them from an imprint's session.

## Commits

The foundry is committed and pushed once every imprint's suite passes. An imprint is committed by pathspec and pushed on Mischa's word.
