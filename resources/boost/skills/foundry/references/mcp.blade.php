# Agents over MCP

With `imprint.mcp` set in `config/imprint.php` and laravel/passport installed, the foundry serves the consent screen an agent's OAuth sign-in shows, hardens laravel/mcp's OAuth routes, and gives the settings page a panel to connect and disconnect agents. {{ config('imprint.mcp') ? config('imprint.name').' connects agents through the foundry.' : config('imprint.name').' does not: nothing here applies until it sets `imprint.mcp`.' }}

## Taking it on

1. laravel/passport and laravel/mcp, with `Mcp::oauthRoutes()` and `Mcp::web('/mcp', ...)` in `routes/ai.php`, the server behind `auth:api` and `throttle:api`. `SESSION_DRIVER=database`: the session holds the serialized authorization request between the consent screen and its answer, and under the `cookie` driver it passes the 4 KB a browser keeps.
2. In `config/imprint.php`, `'mcp' => ['may' => [...], 'note' => '...']`, or `true` for the screen without them.
3. `use Steddle\Foundry\Mcp\HasImprintIcons;` on the MCP server, in place of its `#[Icon]` attributes.
4. Delete what the foundry now does: `resources/views/mcp/authorize.blade.php` and `Passport::authorizationView`, `Passport::$deviceCodeGrantEnabled`, the `api` limiter, the throttled `oauth/register` route, a metadata controller of its own, and the settings page's MCP panel and token revocation on deletion.

## What the foundry does then

- `Passport::authorizationView` is `foundry::mcp.authorize`, on `foundry:layouts.auth`: the client's name without the server Claude Code adds in brackets, 'Claude Code (bron)', the hosts its redirect URIs name, since anyone can register a client under any name, the account, what it may do, and Cancel or Allow access through Passport's own routes.
- Passport's device-code grant is off, and its routes absent.
- `POST oauth/register`, laravel/mcp's open client registration, takes ten an hour per address, counted apart from the address's other requests.
- `/.well-known/oauth-authorization-server` carries `op_logo_uri`, the imprint's `icon-512.png`, and `service_documentation` where `imprint.mcp.docs` names a page.
- The `api` limiter: 120 a minute, by account where one is signed in, by address otherwise.
- `HasImprintIcons` hands the server the two icons `foundry:assets` draws where `imprint.mcp_icons` names them, one for each theme, and the favicon.

## Its own on top

| Key under `imprint.mcp` | What it sets |
|---|---|
| `may` | The lines under 'It may', each a translation key or the line itself |
| `note` | A line under them: what it can never do, or where its calls are logged |
| `docs` | The path of the page on connecting an agent, for the metadata |

## The settings page

- `foundry:agent-connections :$agents` on a Livewire page whose component uses `Steddle\Foundry\Concerns\ManagesAgentConnections`: the server's URL and the `claude mcp add` command to copy, then each client that can still reach the account, one row per client, with Disconnect, which revokes its access tokens and their refresh tokens.
- `Steddle\Foundry\Auth\DeleteAccount`, invoked with the account, signs it out and deletes it with its Passport tokens and their refresh tokens, found by `user_id` since `$user->tokens()` skips a token whose client is gone, its Sanctum tokens and its passkeys, each where the package is installed. The page redirects after.

## Tests

The site's suite, where it sets `imprint.mcp` and has Passport, renders the consent screen through Passport's binding, checks the device routes are absent, and, where laravel/mcp's routes stand, the registration throttle and the icon in the metadata. Where it does not, the test is skipped.
