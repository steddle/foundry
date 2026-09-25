---
name: laravel-code-simplifier
description: "Simplify recently changed PHP, Laravel and Blade code without changing its behaviour: less code, fewer comments, no residue of what was removed. Use after writing or changing code, before a commit, or when asked to simplify or clean up. Works on the session's diff unless told to look wider."
---

# Laravel code simplifier

Make the changed code shorter and plainer while it does exactly what it did. The suite passes before and after.

## Scope

The diff: `git diff` plus new files you created this session. Leave lines you didn't change alone, except where the diff made them dead. Other sessions work in the same tree, so never touch a file you didn't change.

## What to cut

- **Comments that say nothing the code doesn't.** A docblock that restates the method name, narrates a class's purpose, or explains the obvious goes. A docblock stays for types PHP can't express (array shapes, generics, `@property`). A comment stays only for a trap, a constraint or a version quirk a competent reader would otherwise break.
- **Residue.** Anything that describes the past: 'was X', 'no longer', 'removed because', `// legacy`, commented-out code, compatibility shims nobody asked for, config and lang keys nothing reads, tests of behaviour that no longer exists.
- **Orphans the diff created.** Imports, variables, methods, views, routes and translations the change left unused.
- **Speculation.** Options with one caller, abstractions with one use, error handling for cases that can't occur, defensive checks the types already guarantee.
- **Indirection.** A variable used once where the expression reads fine inline; a private method called once that only hides three lines.

## What to prefer

- Laravel's own helpers and conventions over hand-rolled equivalents: collections, `when()`, `firstOrCreate`, route model binding, form requests, policies.
- `match` over nested ternaries or long if/else chains.
- Early returns over nesting.
- Explicit return and parameter types, constructor property promotion.
- Blade components the project already has (in a Steddle imprint: `foundry:*`, Flux) over raw markup.

## What not to do

- Change behaviour, public signatures, routes, config keys other code reads, or copy a reader sees.
- Trade clarity for fewer lines: no dense one-liners, no clever chains.
- Add anything: no new comments, docblocks, abstractions or tests beyond what a simplification needs.
- Spread over parallel agents. Work through the files yourself.

## Finish

1. Run the formatter on the files you changed (`vendor/bin/pint a.php b.php`), never `--dirty`.
2. Run the tests that cover the changed files, then the suite.
3. Report per file what you cut, in one line each.
