# The Steddle account

With `imprint.account` set in `config/imprint.php`, the imprint signs in through the Steddle account at `auth.steddle.com`: one account for every Steddle product, which holds the address, the name, the language, the passkeys, onboarding and `/account`. The imprint keeps a `users` row per account, linked by `steddle_id`, and its own roles and data. {{ config('imprint.account') ? config('imprint.name').' signs in through it, as the client `'.config('imprint.account.client').'`.' : config('imprint.name').' does not: nothing here applies until it sets `imprint.account`.' }}

## Taking it on

1. `composer require laravel/socialite`.
2. `use Steddle\Foundry\Concerns\HasSteddleAccount;` on the `User` model.
3. `php artisan vendor:publish --tag=foundry-account`, then `php artisan migrate`: `steddle_id` on `users`, nullable and unique, and `users.password` and the `password_reset_tokens` table dropped where they exist. The account signs in on auth, and a row made at a first sign-in has no password.
4. In `config/imprint.php`, `'account' => ['client' => '{{ config('imprint.account.client') ?? 'name' }}', 'secret' => env('STEDDLE_ACCOUNT_SECRET'), 'server' => env('STEDDLE_ACCOUNT_URL', 'https://auth.steddle.com'), 'home' => '/dashboard']`. The client and its secret are the ones auth's `config/clients.php` holds for the imprint.
5. `SESSION_DRIVER=database`, so signing out everywhere reaches the imprint's sessions.
6. Delete what the account now does: `imprint.auth`, unless the imprint mails sign-in links of its own, which keep signing in (`references/sign-in.md`), and `imprint.onboarding`, Fortify and its config and provider, `PasskeyUser` and `PasskeyAuthenticatable` on `User`, the `passkeys` table, `onboarded_at`, and in settings `foundry:passkeys` and the delete button, where `foundry:steddle-account` stands instead.

## What the foundry does then

- `login` (GET, guests) redirects to auth's `/oauth/authorize` with Passport's parameters, PKCE S256 and a state, `ui_locales` from the page's language and `login_hint` from `?email=`. An account auth has signed in already comes straight back: that is the single sign-on.
- `foundry.account.callback` (GET `foundry/account/callback`) trades the code for the account at `/oauth/token`, with the secret and the verifier. It finds the row by `steddle_id`, else a row with the address and no `steddle_id`, which it links, else it makes one; sets `email`, `name`, `locale` where the table has the column, and `email_verified_at`; signs it in remembered; puts `['kind' => 'passkey'|'magic_link'|'session', 'link_id' => ?string]` in the session under `credential`, what the account signed in with, by reference; and goes to the intended page, else `imprint.account.home`, else `/`. An error from auth, a state not the session's or auth unreachable shows a page that offers another try, and signs no one in: there is no way in while auth is down, and a signed-in session keeps working.
- `logout` (POST) signs out of this imprint alone.
- `foundry.account.events` (POST `foundry/account/events`, no session, no CSRF) takes what auth tells the imprint, signed with the client's secret: `Steddle-Timestamp` and `Steddle-Signature: sha256=` over `timestamp.body`, refused at 403 when it does not match or is more than 300 seconds off. `updated` fills the row's address, name and language; `left` signs the account out everywhere, as `signed-out` does, then runs `Steddle\Foundry\Auth\DeleteAccount`; `signed-out` deletes the account's rows in `sessions` and rotates its remember token. An account the imprint has no row for answers 200.
- The `staff` gate, in every imprint, with or without the account: a verified address on `@steddle.com`.
- `foundry:steddle-account`, the settings panel that leads to `/account` on auth, where the account changes its name or address, leaves {{ config('imprint.name') }}, signs out everywhere or is deleted.
- `@include('foundry::legal.account')` in the privacy policy, where it lists what it collects: the account's part, in English or Dutch by the page's language.

## Tests

The site's suite, where it sets `imprint.account`, signs an account in through a faked auth and checks the link, the remember cookie and the credential, then sends the events signed and unsigned. The `staff` gate is tested everywhere.
