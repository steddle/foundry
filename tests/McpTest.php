<?php

use Flux\FluxServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Laravel\Mcp\Facades\Mcp;
use Laravel\Mcp\Schema\Icon;
use Laravel\Mcp\Server\McpServiceProvider;
use Laravel\Passkeys\Contracts\PasskeyUser;
use Laravel\Passkeys\PasskeyAuthenticatable;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;
use Laravel\Passport\PassportServiceProvider;
use Livewire\Component;
use Livewire\Livewire;
use Livewire\LivewireServiceProvider;
use Steddle\Foundry\Auth\DeleteAccount;
use Steddle\Foundry\Concerns\ManagesAgentConnections;
use Steddle\Foundry\Mcp\Agents;
use Steddle\Foundry\Mcp\HasImprintIcons;
use Steddle\Foundry\Tests\TestCase;

beforeAll(function () {
    TestCase::$config = [
        'imprint.mcp' => ['may' => ['Read the board and the records', 'Add and close tasks'], 'note' => 'It can delete nothing.'],
        'cache.default' => 'array',
    ];
    TestCase::$providers = [LivewireServiceProvider::class, FluxServiceProvider::class, PassportServiceProvider::class, McpServiceProvider::class];
});

afterAll(function () {
    TestCase::$config = [];
    TestCase::$providers = [];
    Passport::$deviceCodeGrantEnabled = true;
});

beforeEach(function () {
    $this->artisan('view:clear');

    $key = openssl_pkey_new(['private_key_bits' => 2048]);
    openssl_pkey_export($key, $private);

    config([
        'imprint.name' => 'Imprint',
        'imprint.stylesheet' => null,
        'auth.providers.users.model' => McpUser::class,
        'passport.private_key' => $private,
        'passport.public_key' => openssl_pkey_get_details($key)['key'],
    ]);

    Schema::create('users', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('email');
        $table->rememberToken();
    });

    foreach (glob(dirname(__DIR__).'/vendor/laravel/{passport,sanctum}/database/migrations/*.php', GLOB_BRACE) as $migration) {
        (require $migration)->up();
    }

    Schema::create('passkeys', function (Blueprint $table): void {
        $table->id();
        $table->foreignId('user_id');
        $table->string('name');
        $table->timestamps();
    });

    Mcp::oauthRoutes();
    Route::get('login', fn () => 'Log in')->name('login');
    Route::getRoutes()->refreshNameLookups();

    $this->withoutVite();
});

afterEach(function () {
    $this->artisan('view:clear');
});

class McpUser extends Authenticatable implements PasskeyUser
{
    use PasskeyAuthenticatable;

    protected $table = 'users';

    protected $guarded = [];

    public $timestamps = false;

    /** The column an imprint's `App\Models\User` is keyed by elsewhere, `passkeys.user_id` among them. */
    public function getForeignKey(): string
    {
        return 'user_id';
    }
}

function mcpUser(string $email = 'ada@imprint.test'): McpUser
{
    return McpUser::create(['name' => 'Ada Visser', 'email' => $email]);
}

function agent(string $name = 'Claude Code (imprint)', string $redirect = 'http://localhost:33418/callback'): Client
{
    return app(ClientRepository::class)->createAuthorizationCodeGrantClient($name, [$redirect], confidential: false);
}

/**
 * @param  array<string, mixed>  $token
 * @param  array<string, mixed>  $refresh
 */
function grant(McpUser $user, Client $client, array $token = [], array $refresh = []): string
{
    $id = Str::random(40);

    DB::table('oauth_access_tokens')->insert([...['id' => $id, 'user_id' => $user->id, 'client_id' => $client->id, 'revoked' => false, 'created_at' => now()->subDays(2), 'expires_at' => now()->addHour()], ...$token]);
    DB::table('oauth_refresh_tokens')->insert([...['id' => Str::random(40), 'access_token_id' => $id, 'revoked' => false, 'expires_at' => now()->addMonth()], ...$refresh]);

    return $id;
}

/** @return array<string, string> */
function authorizeQuery(Client $client): array
{
    return [
        'client_id' => $client->id,
        'redirect_uri' => $client->redirect_uris[0],
        'response_type' => 'code',
        'scope' => 'mcp:use',
        'state' => 'the-state',
        'code_challenge' => rtrim(strtr(base64_encode(hash('sha256', str_repeat('v', 64), true)), '+/', '-_'), '='),
        'code_challenge_method' => 'S256',
    ];
}

test('the consent screen names the client without its server, where it receives its access, and what it may do', function () {
    $client = agent();

    $this->actingAs(mcpUser())->get(route('passport.authorizations.authorize', authorizeQuery($client)))->assertOk()
        ->assertSee('>Connect Claude Code | Imprint</title>', false)
        ->assertSee('Connect Claude Code to Imprint')
        ->assertDontSee('(imprint)')
        ->assertSee('receives its access at <strong class="font-semibold text-zinc-950 dark:text-zinc-50">localhost</strong>', false)
        ->assertSee('Ada Visser')
        ->assertSeeInOrder(['It may', 'Read the board and the records', 'Add and close tasks', 'It can delete nothing.'])
        ->assertSee('action="'.route('passport.authorizations.approve').'"', false)
        ->assertSee('name="auth_token" value="'.session('authToken').'"', false)
        ->assertSee('<meta name="robots" content="noindex" />', false);
});

test('the consent screen takes one press: either button disables both, and only the pressed one shows its spinner', function () {
    $this->actingAs(mcpUser())->get(route('passport.authorizations.authorize', authorizeQuery(agent())))->assertOk()
        ->assertSee('x-data="{ sent: null }"', false)
        ->assertSeeInOrder(['x-on:submit="sent = \'deny\'"', 'name="auth_token"', '<fieldset x-bind:disabled="sent"', 'x-bind:disabled="sent === \'deny\'"'], false)
        ->assertSeeInOrder(['x-on:submit="sent = \'approve\'"', 'name="auth_token"', '<fieldset x-bind:disabled="sent"', 'x-bind:disabled="sent === \'approve\'"'], false);
});

test('allowing access hands the client its code, and cancelling hands it a refusal', function () {
    $client = agent();
    $user = mcpUser();

    $this->actingAs($user)->get(route('passport.authorizations.authorize', authorizeQuery($client)));
    $approved = $this->post(route('passport.authorizations.approve'), ['auth_token' => session('authToken')]);

    expect($approved->headers->get('Location'))->toStartWith('http://localhost:33418/callback?code=')->toContain('state=the-state');

    $this->get(route('passport.authorizations.authorize', [...authorizeQuery($client), 'prompt' => 'consent']));
    $denied = $this->delete(route('passport.authorizations.deny'), ['auth_token' => session('authToken')]);

    expect($denied->headers->get('Location'))->toStartWith('http://localhost:33418/callback?')->toContain('error=access_denied');
});

test('the consent screen leaves out what the imprint does not say', function () {
    config(['imprint.mcp' => true]);

    $this->actingAs(mcpUser())->get(route('passport.authorizations.authorize', authorizeQuery(agent())))->assertOk()
        ->assertSee('Connect Claude Code to Imprint')
        ->assertDontSee('It may');
});

test('the device-code grant is off, its routes absent', function () {
    expect(Passport::$deviceCodeGrantEnabled)->toBeFalse()
        ->and(Route::has('passport.device'))->toBeFalse()
        ->and(Route::has('passport.device.code'))->toBeFalse();
});

test('open client registration takes ten an hour per address, counted apart from the address\'s other requests', function () {
    $register = fn () => $this->postJson('/oauth/register', ['client_name' => 'Claude Code', 'redirect_uris' => ['http://localhost:33418/callback']]);

    $register()->assertHeader('X-RateLimit-Limit', '10');

    foreach (range(2, 10) as $attempt) {
        $register();
    }

    $register()->assertTooManyRequests();
    $this->get('/.well-known/oauth-authorization-server')->assertOk();
});

test('the authorization server\'s metadata carries the imprint\'s icon, and its docs on connecting where it names them', function () {
    $this->getJson('/.well-known/oauth-authorization-server')->assertOk()
        ->assertJsonPath('op_logo_uri', asset('icon-512.png'))
        ->assertJsonPath('token_endpoint', route('passport.token'))
        ->assertJsonMissingPath('service_documentation');

    config(['imprint.mcp.docs' => '/docs/connect']);

    $this->getJson('/.well-known/oauth-authorization-server/mcp')->assertOk()
        ->assertJsonPath('op_logo_uri', asset('icon-512.png'))
        ->assertJsonPath('service_documentation', url('/docs/connect'));
});

test('the api limiter takes 120 a minute, by account where one is signed in and by address otherwise', function () {
    $request = Request::create('/api/probe', server: ['REMOTE_ADDR' => '10.0.0.1']);
    $limit = RateLimiter::limiter('api')($request);

    expect($limit->maxAttempts)->toBe(120)->and($limit->key)->toBe('10.0.0.1');

    $user = mcpUser();
    $request->setUserResolver(fn () => $user);

    expect(RateLimiter::limiter('api')($request)->key)->toBe($user->id);
});

test('an MCP server takes the imprint\'s icons from mcp_icons, for each theme and the favicon at any size', function () {
    $server = new class
    {
        use HasImprintIcons;

        /** @return list<array<string, mixed>> */
        public function listed(): array
        {
            return array_map(fn (Icon $icon): array => $icon->toArray(), $this->icons());
        }
    };

    expect($server->listed())->toBe([]);

    config(['imprint.mcp_icons' => 'bron']);

    expect($server->listed())->toBe([
        ['src' => asset('/bron-128.png'), 'mimeType' => 'image/png', 'sizes' => ['128x128'], 'theme' => 'light'],
        ['src' => asset('/bron-light-128.png'), 'mimeType' => 'image/png', 'sizes' => ['128x128'], 'theme' => 'dark'],
        ['src' => asset('/favicon.svg'), 'mimeType' => 'image/svg+xml', 'sizes' => ['any']],
    ]);
});

test('the server\'s URL and the command that adds it are named after the imprint', function () {
    config(['imprint.name' => 'Send NDA']);

    expect(Agents::url())->toBe(url('mcp'))
        ->and(Agents::command())->toBe('claude mcp add --transport http send-nda '.url('mcp'))
        ->and(Agents::name('Claude Code (send-nda)'))->toBe('Claude Code')
        ->and(Agents::name('Claude'))->toBe('Claude')
        ->and(Agents::hosts(['https://claude.ai/api/callback', 'https://claude.ai/other', 'http://localhost:1/cb']))->toBe('claude.ai, localhost');
});

test('deleting an account signs it out and takes its tokens, their refresh tokens, its Sanctum tokens and its passkeys with it', function () {
    $user = mcpUser();
    $other = mcpUser('bo@imprint.test');
    $gone = agent('Gone');
    grant($user, agent());
    grant($user, $gone);
    grant($other, agent());
    $gone->delete();

    DB::table('personal_access_tokens')->insert([
        ['tokenable_type' => McpUser::class, 'tokenable_id' => $user->id, 'name' => 'Claude Desktop', 'token' => Str::random(64), 'abilities' => '["mcp:use"]'],
        ['tokenable_type' => McpUser::class, 'tokenable_id' => $other->id, 'name' => 'Claude Desktop', 'token' => Str::random(64), 'abilities' => '["mcp:use"]'],
    ]);
    DB::table('passkeys')->insert([['user_id' => $user->id, 'name' => 'Chrome on Mac'], ['user_id' => $other->id, 'name' => 'Safari']]);

    $this->actingAs($user);
    app(DeleteAccount::class)($user);

    $this->assertGuest();
    expect(McpUser::pluck('email')->all())->toBe(['bo@imprint.test'])
        ->and(DB::table('oauth_access_tokens')->pluck('user_id')->all())->toBe([$other->id])
        ->and(DB::table('oauth_refresh_tokens')->count())->toBe(1)
        ->and(DB::table('personal_access_tokens')->pluck('tokenable_id')->all())->toBe([$other->id])
        ->and(DB::table('passkeys')->pluck('user_id')->all())->toBe([$other->id]);
});

class AgentsProbe extends Component
{
    use ManagesAgentConnections;

    public function render(): string
    {
        return '<div><foundry:agent-connections :$agents /></div>';
    }
}

test('the connections list each client that can still reach the account once, and disconnecting one revokes its tokens and their refresh tokens', function () {
    $user = mcpUser();
    $live = agent('Claude Code (imprint)');
    $refreshing = agent('Claude', 'https://claude.ai/api/mcp/auth_callback');
    grant($user, $live);
    grant($user, $live);
    grant($user, $refreshing, ['expires_at' => now()->subDay()]);
    grant($user, agent('Revoked'), ['revoked' => true]);
    grant($user, agent('Lapsed'), ['expires_at' => now()->subDay()], ['expires_at' => now()->subDay()]);
    grant(mcpUser('bo@imprint.test'), agent('Someone else\'s'));

    $component = Livewire::actingAs($user)->test(AgentsProbe::class)
        ->assertSee(url('mcp'))
        ->assertSee('claude mcp add --transport http imprint '.url('mcp'))
        ->assertSee('Connected 2 days ago | localhost')
        ->assertSee('Connected 2 days ago | claude.ai')
        ->assertDontSee('Revoked')
        ->assertDontSee('Lapsed')
        ->assertDontSee('Someone else');

    expect(collect($component->get('agents'))->pluck('name')->sort()->values()->all())->toBe(['Claude', 'Claude Code']);

    $component->call('disconnectAgent', $live->id);

    expect(collect($component->get('agents'))->pluck('name')->all())->toBe(['Claude'])
        ->and(DB::table('oauth_access_tokens')->where('client_id', $live->id)->pluck('revoked')->unique()->all())->toBe([1])
        ->and(DB::table('oauth_refresh_tokens')->whereIn('access_token_id', DB::table('oauth_access_tokens')->where('client_id', $live->id)->select('id'))->pluck('revoked')->unique()->all())->toBe([1]);
});

test('the connections panel says so where no agent is connected', function () {
    expect(Blade::render('<foundry:agent-connections :agents="[]" />', deleteCachedView: true))
        ->toContain('No agents connected yet.')
        ->toContain('value="'.url('mcp').'"')
        ->toContain('font-mono');
});
