# Onboarding

An account a magic link, an invitation or a guest send made carries the local part of its address as its name. With `imprint.onboarding` set in `config/imprint.php`, the foundry sends such an account to `/welcome` before any signed-in page, where it gives its full name once, and then on to the page it asked for. {{ config('imprint.onboarding') ? config('imprint.name').' onboards.' : config('imprint.name').' does not onboard: nothing here applies until it sets `imprint.onboarding`.' }}

## Taking it on

1. `use Steddle\Foundry\Concerns\HasOnboarding;` on the `User` model: `onboarded_at`, `hasOnboarded()`, and `hasPlaceholderName()`, true for no name or the local part of the address.
2. `php artisan vendor:publish --tag=foundry-onboarding`, then `php artisan migrate`: it adds `onboarded_at`, and marks every account with a name of its own onboarded.
3. In `config/imprint.php`, `'onboarding' => ['home' => '/dashboard']`, or `true` to go home to `/`.
4. Delete any onboarding of the imprint's own: its middleware, its welcome page and route, and `passport.middleware` entries naming them.

## What the foundry does then

- `Steddle\Foundry\Http\Middleware\EnsureUserIsOnboarded` runs on every route that authenticates, as it declares `auth` or through a group of its own, and on Passport's routes through `passport.middleware`, so the consent screen of an agent signing in sends the account through `/welcome` and back. It runs after authentication, so a guest is sent to log in first. A request without an account, or with one that does not use the concern, passes.
- On a GET it keeps the address as the intended one; `/welcome` itself, `logout` and Livewire's updates pass, and so does a request with no session or one asking for JSON: a token's call to the API or the MCP goes through.
- `/welcome` (`foundry.welcome`) is a Livewire page in the imprint's own `layouts::auth`: one field, the full name, `autocomplete="name"`. Saving sets `name` and `onboarded_at`, and goes to the intended page, or `imprint.onboarding.home`.

## Options

Under `imprint.onboarding`:

| Key | What it sets |
|---|---|
| `home` | Where an account goes after, when it asked for no page |
| `prefill` | An invokable class of the imprint's own, handed the account, returning the name it already knows or null, such as the name on an NDA sent to it |
| `next` | A route name for a step of the imprint's own after the name, a phone number for instance; it ends with `redirect()->intended(...)`, and the intended page waits for it |

## Tests

The site's suite, where it onboards, sends an account named after its address through a signed-in page and, with Passport, its consent screen, to `/welcome`, and back after saving. Where it does not onboard, the test is skipped.
