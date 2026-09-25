# Sign-in

With `imprint.auth` set in `config/imprint.php`, the foundry signs a reader in with a link by email or a passkey, and keeps them signed in: both set the remember cookie, which outlasts `SESSION_LIFETIME`. {{ config('imprint.auth') ? config('imprint.name').' signs in through the foundry'.(config('imprint.auth.signup') ? ', and the first link to an address makes its account.' : ', for existing accounts only.') : config('imprint.name').' does not: nothing here applies until it sets `imprint.auth`.' }}

## Taking it on

1. Fortify with `Features::passkeys(['confirmPassword' => false])`, and `fortify.home`: where a sign-in lands, and where a signed-in visitor of a guest page goes.
2. `php artisan vendor:publish --tag=foundry-sign-in`, then `php artisan migrate`: the `magic_links` table. Schedule `model:prune --model="Steddle\Foundry\Auth\MagicLink"`; `model:prune` looks in `app/Models` only.
3. In `config/imprint.php`, `'auth' => ['signup' => true]`, or `false` to mail existing accounts only.
4. Delete what the foundry now does: the imprint's magic-link controller, model, notification and routes, `auth/login` and `auth/confirm-login` views, its `login`, `magic-link` and `passkeys` limiters and `limiters` in `config/fortify.php`, `Fortify::loginView` and `confirmPasswordView`, `Noindex` in `fortify.middleware`, `redirectUsersTo`, and `.well-known/passkey-endpoints`.

## What the foundry does then

- `login` is Fortify's route with the foundry's view on `foundry:layouts.auth` as a card: the imprint's lockup over it, leading home, and at the page's foot Terms and Privacy where the imprint publishes them, and steddle.com. One field, `autocomplete="email webauthn"` so the browser offers a passkey in it, and `foundry:passkey-sign-in` under it where passkey sign-in is routed. After a send, a card with the address stands in for the form. Without signup the card reads 'if this address has an account', so the answer is the same either way.
- `login.magic.send` (POST `login/magic`) mails `Steddle\Foundry\Auth\LoginLink`, queued on `imprint.auth.queue` where set, in the reader's language, on the mail theme and its greeting, with a line to add a passkey for an account that has none. The link is signed per account and works once, for 15 minutes.
- `login.magic` (GET `login/magic/{user}`) shows a page that posts itself with a spinner, and a `noscript` button. It spends nothing: a mail scanner that fetches the link runs no script. `login.magic.consume` (POST, same path) spends the token under a lock, signs out another account first, signs the link's account in with remember, marks its address verified, and redirects.
- A used or expired link says so and offers a new one.
- The `login`, `magic-link` and `passkeys` limiters, the last by IP alone: a credential id is the client's to choose. Fortify's login and passkey routes throttle on them where `fortify.limiters` names none of the imprint's own. Fortify's routes carry `Noindex`, and its password confirmation goes to `login`.
- `/.well-known/passkey-endpoints` answers with the `settings` route, where the imprint has one.

## Its own on top

| Key under `imprint.auth` | What it sets |
|---|---|
| `signup` | Whether the first link to an address makes its account |
| `redirect` | An invokable class of the imprint's own, handed the `MagicLink` and the request after the sign-in, returning a redirect or null |
| `client` | An invokable class of the imprint's own, handed the request, returning `['name' => ..., 'icon' => ?url, 'email' => ?string, 'palette' => ?string, 'lockup' => ?url, 'icons' => ?url, 'ink' => ?colour]` or null. The login page's heading, description and 48px icon above the heading, and the mail, sign in to `name`; the mail comes from `name` on `mail.from.address`; `email` fills the address field. `foundry:layouts.auth` takes the rest, on the login page and wherever else it renders, the link's confirm page and the consent screen among them: `palette` on the page, `lockup` as an image over the card, `icons` as the base URL of `favicon.svg`, `favicon-96x96.png` and `apple-touch-icon.png`, `ink` as the browser chrome, and `name` in `<title>`. Null, or a key that is null, leaves the imprint's own |
| `note` | An invokable class of the imprint's own, handed the request, returning one line for the foot of the login card, small and muted over a rule, or null for none |
| `queue` | The queue the link's mail goes out on |
| `keep` | Days a link's row outlives its expiry before `model:prune` removes it, 1 by default |

- The redirect: `redirect` where it answers, else the link's `intended`, else the session's intended URL, else `imprint.account.home` beside the Steddle account, else `fortify.home`. A send stores the session's intended URL on the row, so a link opened in another browser still reaches the page it was sent from.
- A link of the imprint's own: `MagicLink::issue($user, 'nda', intended: $url, expiresAt: now()->addDays(7), attributes: ['contract_id' => $id])` returns the row with its `url`; columns of the imprint's own come in with a migration of its own. Mail it with a notification of its own, and resolve its `purpose` in `redirect`.
- Beside `imprint.account`, where the Steddle account signs readers in, `imprint.auth` keeps only the links of the imprint's own: `login.magic` and `login.magic.consume` with the `magic-link` limiter, and `redirect`. No `login.magic.send`, no `.well-known/passkey-endpoints`, no Fortify login view or password confirmation, and `login` is the account's.
- Copy: `lang/vendor/foundry/{locale}/sign-in.php` overrides a line. Markup: `resources/views/vendor/foundry/auth/login.blade.php` or `confirm.blade.php`; the confirm view is handed `$usable`, `$action` and `$link`, the row or null.

## Tests

The site's suite, where it sets `imprint.auth` without `imprint.account`, renders the login page, sends a link to an account, opens it and posts it, and checks the remember cookie. Where it does not, the test is skipped.
