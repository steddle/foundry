<?php

use Flux\FluxServiceProvider;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Livewire\Component;
use Livewire\Livewire;
use Livewire\LivewireServiceProvider;
use Steddle\Foundry\Concerns\HasInitials;

beforeEach(function () {
    $this->artisan('view:clear');
    $directory = resource_path('views/foundry');
    File::ensureDirectoryExists($directory);
    File::put($directory.'/lockup.blade.php', '<span {{ $attributes }}>Imprint</span>');
    File::put($directory.'/mark.blade.php', '<svg {{ $attributes }}></svg>');
    config(['imprint.name' => 'Imprint', 'imprint.stylesheet' => null]);
    $this->user = (object) ['name' => 'Ada Visser', 'email' => 'ada@example.com'];
});

afterEach(function () {
    File::deleteDirectory(resource_path('views/components'));
    File::deleteDirectory(resource_path('views/foundry'));
    // A test here registers Livewire, and a view compiled under it calls Livewire when a later test renders it.
    $this->artisan('view:clear');
});

test('a signed-in page is a noindex document under the imprint\'s bar, its content edge to edge, with the toasts', function () {
    File::put(resource_path('views/foundry/bar.blade.php'), '<nav id="the-imprint-bar"></nav>');

    $html = Blade::render('<foundry:layouts.app title="Agreements"><p>Body</p></foundry:layouts.app>', deleteCachedView: true);

    expect($html)
        ->toContain('>Agreements | Imprint</title>')
        ->toContain('<meta name="robots" content="noindex" />')
        ->toContain('<nav id="the-imprint-bar"></nav>')
        ->toContain('<flux:toast.group>')
        ->and((string) str($html)->between('<main class="flex-1">', '</main>'))->toContain('<p>Body</p>')->not->toContain('py-10');
});

test('the foundry\'s bar lies over the band a signed-in page opens on', function () {
    $html = Blade::render('<foundry:layouts.app title="x"><foundry:app.band><p>Band</p></foundry:app.band></foundry:layouts.app>', deleteCachedView: true);

    expect($html)->toContain('<header class="absolute inset-x-0 top-0 z-10 ink">')
        ->and((string) str($html)->between('<main class="flex-1">', '</main>'))->toContain('bg-zinc-900 ink grain')->toContain('<p>Band</p>');
});

test('the account menu opens on the reader\'s name and address, the imprint\'s items and logging out', function () {
    Route::post('logout', fn () => null)->name('logout');
    Route::getRoutes()->refreshNameLookups();

    $html = Blade::render('<foundry:account-menu :user="$user"><flux:menu.item href="/settings">Settings</flux:menu.item></foundry:account-menu>', ['user' => $this->user], deleteCachedView: true);

    expect($html)
        ->toContain('aria-label="Account menu for Ada Visser"')
        ->toContain('<flux:avatar as="button"')
        ->toContain('ada@example.com')
        ->toContain('href="/settings"')
        ->toContain('action="'.url('/logout').'"')
        ->toContain('name="_token"')
        ->toContain('Sign out');
});

test('the account menu leaves out logging out where the imprint has no logout route', function () {
    $html = Blade::render('<foundry:account-menu :user="$user" />', ['user' => $this->user], deleteCachedView: true);

    expect($html)->toContain('Ada Visser')->not->toContain('Sign out');
});

test('an ink page sets its toast group through foundry:toasts', function () {
    $html = Blade::render('<foundry:layouts.auth title="Sign in" description="Sign in." card>Form</foundry:layouts.auth>', deleteCachedView: true);

    expect($html)->toContain('<flux:toast.group>')->toContain('Form')->toContain('[&_h1]:text-3xl!');
});

test('a record row keeps its actions outside its link', function () {
    $html = Blade::render('<foundry:record-row href="/agreements/12" title="Mutual NDA" meta="Sent 21 Sep 2026"><x-slot:status><span>Signed</span></x-slot:status><x-slot:actions><button>Resend</button></x-slot:actions></foundry:record-row>', deleteCachedView: true);

    $link = str($html)->after('<a href="/agreements/12"')->before('</a>');

    expect((string) $link)->toContain('Mutual NDA')->toContain('Sent 21 Sep 2026')->toContain('Signed')->not->toContain('Resend')
        ->and((string) str($html)->after('</a>'))->toContain('<button>Resend</button>');
});

test('a page head sets the trail above it, the status beside the title and the actions', function () {
    $html = Blade::render('<foundry:page-head :breadcrumbs="[\'Agreements\' => \'/agreements\']" title="Mutual NDA" lead="Sent today."><x-slot:status><span>Signed</span></x-slot:status><x-slot:actions><a href="/pdf">Download</a></x-slot:actions></foundry:page-head>', deleteCachedView: true);

    expect($html)
        ->toContain('<flux:breadcrumbs data-markdown-skip')
        ->toContain('>Agreements</flux:breadcrumbs.item>')
        ->toContain('Mutual NDA')
        ->toContain('<span>Signed</span>')
        ->toContain('Sent today.')
        ->toContain('<a href="/pdf">Download</a>');
});

test('a signed-in page takes a bar of its own in place of foundry:bar', function () {
    $html = Blade::render('<foundry:layouts.app title="Agreements"><x-slot:nav><header>Own bar</header></x-slot:nav><p>Body</p></foundry:layouts.app>', deleteCachedView: true);

    expect($html)->toContain('<header>Own bar</header>')->toContain('<p>Body</p>')->not->toContain('aria-label="Imprint, home"');
});

test('a page head leads back to the one page above it', function () {
    $html = Blade::render('<foundry:page-head :back="[\'All agreements\' => \'/agreements\']" title="Mutual NDA" />', deleteCachedView: true);

    expect($html)
        ->toContain('<a href="/agreements"')
        ->toContain('<flux:icon.arrow-left variant="micro" />All agreements')
        ->not->toContain('<flux:breadcrumbs');
});

test('a field row links its label to its control', function () {
    $html = Blade::render('<foundry:field-row label="Email" description="Where links go." for="email"><input id="email" /></foundry:field-row>', deleteCachedView: true);

    expect($html)->toContain('<label for="email"')->toContain('<input id="email" />')->toContain('Where links go.');
});

test('a confirm item asks twice in the menu and acts on the third press', function () {
    $html = Blade::render('<foundry:confirm-item icon="trash" action="$wire.delete(\'abc\')">Delete</foundry:confirm-item>', deleteCachedView: true);

    expect($html)
        ->toContain('Delete')
        ->toContain('Click again')
        ->toContain('Tap again')
        ->toContain('One more time')
        ->toContain('x-on:click.capture')
        ->toContain("dispatchEvent(new CustomEvent('lofi-close-popovers')); \$wire.delete(&#039;abc&#039;); timer = setTimeout(() => step = 0, 300) }, 240)")
        ->toContain('x-on:lofi-close-popovers="$event.stopPropagation()"')
        ->not->toContain('wire:click');
});

test('a confirm button fills a third per press and acts on the third', function () {
    $html = Blade::render('<foundry:confirm-button label="Delete account" action="$wire.deleteAccount()" />', deleteCachedView: true);

    expect($html)
        ->toContain('aria-label="Delete account"')
        ->toContain('step = 3; timer = setTimeout(() => Promise.resolve().then(() => $wire.deleteAccount())')
        ->toContain('<flux:icon.check variant="micro" />Done')
        ->toContain('clip-path: inset(0 ${100 - (Math.min(step, 3) / 3) * 100}% 0 0)')
        ->toContain('One more time')
        ->not->toContain('wire:click');
});

test('a badge draws a hairline ring in its own ramp', function () {
    expect(Blade::render('<foundry:badge tone="success">Signed</foundry:badge>', deleteCachedView: true))
        ->toContain('ring-1 ring-inset')
        ->toContain('ring-success-700/10');
});

test('a model with HasInitials reads its initials through Nameable', function () {
    $user = new class extends Model
    {
        use HasInitials;

        protected $guarded = [];
    };

    expect($user->fill(['name' => 'Ada Visser (Northwind)'])->initials)->toBe('AV')
        ->and($user->fill(['name' => 'Madonna'])->initials)->toBe('M')
        ->and($user->fill(['name' => 'mischa sigtermans'])->initials)->toBe('MS')
        ->and($user->fill(['name' => 'Playwright recipient 2'])->initials)->toBe('PR');
});

test('a confirm button tells a screen reader each step, and a keyboard press starts no timer', function () {
    $html = Blade::render('<foundry:confirm-button label="Delete account" action="$wire.deleteAccount()" />', deleteCachedView: true);

    expect($html)
        ->toMatch('#</flux:button>\s*<span role="status" class="sr-only"#')
        ->toContain('Press again to confirm')
        ->toContain('if ($event.detail > 0) timer = setTimeout(() => step = 0, 1500)')
        ->toContain('x-on:blur="if (step < 3) { clearTimeout(timer); step = 0 }"');
});

test('a confirm item tells a screen reader each step, and starts over when focus leaves it', function () {
    $html = Blade::render('<foundry:confirm-item icon="trash" action="$wire.delete(\'abc\')">Delete</foundry:confirm-item>', deleteCachedView: true);

    expect($html)
        ->toMatch('#</flux:menu.item>\s*<span role="status" class="sr-only"#')
        ->toContain('Press again to confirm')
        ->toContain('if ($event.detail > 0) timer = setTimeout(() => step = 0, 1500)')
        ->toContain('x-on:focusout="if (step < 3) { clearTimeout(timer); step = 0 }"');
});

test('a signed-in page loads Livewire, Alpine with it, once, with a component on it or without', function (string $content) {
    $this->app->register(LivewireServiceProvider::class);
    Livewire::component('shell-probe', ShellProbe::class);
    Route::get('/shell', fn () => Blade::render('<foundry:layouts.app title="Shell">'.$content.'</foundry:layouts.app>', deleteCachedView: true));

    expect(substr_count($this->get('/shell')->assertOk()->getContent(), 'data-csrf='))->toBe(1);
})->with([
    'a plain page' => ['<p>Body</p>'],
    'a page with a component' => ['<livewire:shell-probe />'],
]);

class ShellProbe extends Component
{
    public function render(): string
    {
        return '<div>Probe</div>';
    }
}

test('the passkeys panel lists each one with its authenticator, when it was added and used, and a way to remove it', function () {
    $this->app->register(LivewireServiceProvider::class);
    $this->withoutVite();

    $html = Blade::render('<foundry:passkeys :$passkeys />', ['passkeys' => [
        ['id' => 7, 'name' => 'Chrome on Mac', 'authenticator' => 'iCloud Keychain', 'created_at' => '2 days ago', 'last_used_at' => '1 hour ago'],
    ]], deleteCachedView: true);

    expect($html)
        ->toContain('Passkeys')
        ->toContain('Chrome on Mac')
        ->toContain('iCloud Keychain')
        ->toContain('Added 2 days ago | last used 1 hour ago')
        ->toContain('$wire.deletePasskey(7)')
        ->toContain('Add a passkey');

    expect(Blade::render('<foundry:passkeys :passkeys="[]" />', deleteCachedView: true))->toContain('No passkeys yet.');
});

test('a rail page is a noindex document with the rail beside the page\'s own landmark, the service line and the toasts, and no bar', function () {
    File::put(resource_path('views/foundry/bar.blade.php'), '<nav id="the-imprint-bar"></nav>');

    $html = Blade::render('<foundry:layouts.app.sidebar title="Overview"><x-slot:sidebar><aside id="the-rail"></aside></x-slot:sidebar><p>Body</p></foundry:layouts.app.sidebar>', deleteCachedView: true);

    expect($html)
        ->toContain('>Overview | Imprint</title>')
        ->toContain('<meta name="robots" content="noindex" />')
        ->toContain('<aside id="the-rail"></aside>')
        ->toContain('<flux:toast.group>')
        ->not->toContain('the-imprint-bar')
        ->and((string) str($html)->between('<main class="[grid-area:main] min-w-0" data-flux-main>', '</main>'))->toContain('<p>Body</p>')
        ->and((string) str($html)->after('</main>'))->toContain('<footer class="[grid-area:footer]">');
});

test('a rail page leaves the service line to a page that sets it itself', function () {
    $html = Blade::render('<foundry:layouts.app.sidebar title="NDA" :footer="false"><x-slot:sidebar><aside></aside></x-slot:sidebar><p>Body</p></foundry:layouts.app.sidebar>', deleteCachedView: true);

    expect($html)->toContain('<p>Body</p>')->not->toContain('<footer');
});

test('the rail sets the lockup, the search, the links under a labelled nav and the reader\'s menu at its foot and in the bar below lg', function () {
    $html = Blade::render(<<<'BLADE'
        <foundry:app.sidebar :user="$user" home="/dashboard">
            <x-slot:search><button id="the-search"></button></x-slot:search>
            <a id="a-link"></a>
            <x-slot:account><flux:menu.item href="/settings">Settings</flux:menu.item></x-slot:account>
        </foundry:app.sidebar>
        BLADE, ['user' => $this->user], deleteCachedView: true);

    $rail = (string) str($html)->between('<flux:sidebar ', '</flux:sidebar>');
    $bar = (string) str($html)->after('</flux:sidebar>');

    expect($rail)
        ->toContain('collapsible="mobile" class="border-e border-zinc-50/13 bg-zinc-900 ink"')
        ->toContain('<a href="/dashboard" aria-label="Imprint, home"')
        ->toContain('<button id="the-search"></button>')
        ->toMatch('#<flux:sidebar.nav [^>]+>\s*<a id="a-link"></a>#')
        ->toContain('<flux:sidebar.profile')
        ->toContain('href="/settings"')
        ->not->toContain('<flux:avatar as="button"')
        ->and($bar)
        ->toContain('<flux:header class="bg-zinc-900 ink lg:hidden">')
        ->toContain('<a href="/dashboard" aria-label="Imprint, home"')
        ->toContain('<flux:avatar as="button"')
        ->toContain('href="/settings"');
});

test('the rail leaves out the account menu without a reader', function () {
    $html = Blade::render('<foundry:app.sidebar><a id="a-link"></a></foundry:app.sidebar>', deleteCachedView: true);

    expect($html)->toContain('<a id="a-link"></a>')->not->toContain('<flux:dropdown')->not->toContain('<flux:sidebar.profile');
});

test('a rail item marks the current page, by its address or as told, and shows its count', function () {
    $this->app->register(LivewireServiceProvider::class);
    $this->app->register(FluxServiceProvider::class);
    Route::get('/inbox', fn () => Blade::render(<<<'BLADE'
        <foundry:app.sidebar.item href="/inbox" icon="inbox" count="12">Inbox</foundry:app.sidebar.item>
        <foundry:app.sidebar.item href="/companies" count="0">Companies</foundry:app.sidebar.item>
        <foundry:app.sidebar.item href="/board/q3" :current="true">Q3 update</foundry:app.sidebar.item>
        BLADE, deleteCachedView: true));

    $links = str($this->get('/inbox')->assertOk()->getContent())->matchAll('#<a href="[^"]+"[^>]*>#');

    expect($links)->toHaveCount(3)
        ->and($links[0])->toContain('href="/inbox"')->toContain('aria-current="page"')->toContain('data-current="data-current"')
        ->and($links[1])->toContain('href="/companies"')->not->toContain('aria-current')->not->toContain('data-current="data-current"')
        ->and($links[2])->toContain('href="/board/q3"')->toContain('aria-current="page"')
        ->and($this->get('/inbox')->getContent())->toMatch('#data-flux-navlist-badge>12</span>#')->not->toMatch('#data-flux-navlist-badge>0</span>#');
});

test('a rail group folds under its heading, and the search reads the foundry\'s word where the imprint names none', function () {
    $this->app->register(LivewireServiceProvider::class);
    $this->app->register(FluxServiceProvider::class);

    $group = Blade::render('<foundry:app.sidebar.group heading="Legal" :expanded="false"><a id="a-link"></a></foundry:app.sidebar.group>', deleteCachedView: true);
    $search = Blade::render('<foundry:app.sidebar.search kbd="⌘K" x-on:click="open()" />', deleteCachedView: true);

    expect($group)->toContain('<ui-disclosure')->not->toMatch('#<ui-disclosure[^>]* open #')->toContain('Legal')->toContain('<a id="a-link"></a>')
        ->and($search)->toContain('Search')->toContain('⌘K')->toContain('x-on:click="open()"');
});

test('the rail\'s account menu opens upward from its foot and the bar\'s downward, and the drawer\'s buttons read open and close', function () {
    $this->app->register(LivewireServiceProvider::class);
    $this->app->register(FluxServiceProvider::class);

    $html = Blade::render('<foundry:app.sidebar :user="$user"><a id="a-link"></a></foundry:app.sidebar>', ['user' => $this->user], deleteCachedView: true);

    expect(str($html)->matchAll('#<ui-dropdown position="([^"]+)"#')->all())->toBe(['top start', 'bottom end'])
        ->and(str($html)->matchAll('#<button[^>]*aria-label="([^"]+)"[^>]*data-flux-sidebar-toggle#')->all())->toBe(['Close menu', 'Menu']);
});

test('a remembered group opens as the cookie says, falls back to expanded, and gives way to open', function (?string $cookie, string $group, bool $opens) {
    $this->app->register(LivewireServiceProvider::class);
    $this->app->register(FluxServiceProvider::class);
    Route::get('/sidebar', fn () => Blade::render($group, deleteCachedView: true));

    $request = $cookie === null ? $this : $this->withUnencryptedCookie('foundry_sidebar', $cookie);
    $disclosure = (string) str($request->get('/sidebar')->assertOk()->getContent())->match('#<ui-disclosure[^>]*>#');

    expect($disclosure)->not->toBeEmpty();
    expect((bool) preg_match('#\sopen(\s|>)#', $disclosure))->toBe($opens);
})->with([
    'closed in the cookie' => ['{"company":false}', '<foundry:app.sidebar.group heading="Company" remember="company"><a></a></foundry:app.sidebar.group>', false],
    'open in the cookie, over expanded' => ['{"company":true}', '<foundry:app.sidebar.group heading="Company" remember="company" :expanded="false"><a></a></foundry:app.sidebar.group>', true],
    'no cookie, expanded' => [null, '<foundry:app.sidebar.group heading="Company" remember="company"><a></a></foundry:app.sidebar.group>', true],
    'no cookie, not expanded' => [null, '<foundry:app.sidebar.group heading="Company" remember="company" :expanded="false"><a></a></foundry:app.sidebar.group>', false],
    'another key only' => ['{"legal":false}', '<foundry:app.sidebar.group heading="Company" remember="company"><a></a></foundry:app.sidebar.group>', true],
    'a value that is no bool' => ['{"company":"no"}', '<foundry:app.sidebar.group heading="Company" remember="company"><a></a></foundry:app.sidebar.group>', true],
    'no JSON' => ['company=false', '<foundry:app.sidebar.group heading="Company" remember="company"><a></a></foundry:app.sidebar.group>', true],
    'a list' => ['[false]', '<foundry:app.sidebar.group heading="Company" remember="company"><a></a></foundry:app.sidebar.group>', true],
    'open over a closed cookie' => ['{"company":false}', '<foundry:app.sidebar.group heading="Company" remember="company" open><a></a></foundry:app.sidebar.group>', true],
    'no key ignores the cookie' => ['{"company":false}', '<foundry:app.sidebar.group heading="Company"><a></a></foundry:app.sidebar.group>', true],
]);

test('a remembered group writes its state to the cookie when it folds, and one without a key writes nothing', function () {
    $this->app->register(LivewireServiceProvider::class);
    $this->app->register(FluxServiceProvider::class);

    $remembered = Blade::render('<foundry:app.sidebar.group heading="Company" remember="company"><a></a></foundry:app.sidebar.group>', deleteCachedView: true);
    $plain = Blade::render('<foundry:app.sidebar.group heading="Company"><a></a></foundry:app.sidebar.group>', deleteCachedView: true);

    expect($remembered)
        ->toContain('x-on:lofi-disclosable-change.self=')
        ->toContain('state[&#039;company&#039;] = $el.value;')
        ->toContain('path=/; max-age=31536000; samesite=lax')
        ->and($plain)->not->toContain('lofi-disclosable-change');
});

test('the sidebar cookie reaches the page unencrypted, so the browser can write it', function () {
    expect((new EncryptCookies(app('encrypter')))->isDisabled('foundry_sidebar'))->toBeTrue();
});

test('an open group\'s chevrons follow its panel, which the server marks, so the first paint points them open', function () {
    $this->app->register(LivewireServiceProvider::class);
    $this->app->register(FluxServiceProvider::class);

    $open = Blade::render('<foundry:app.sidebar.group heading="Board"><a></a></foundry:app.sidebar.group>', deleteCachedView: true);
    $closed = Blade::render('<foundry:app.sidebar.group heading="Legal" :expanded="false"><a></a></foundry:app.sidebar.group>', deleteCachedView: true);

    // The button Flux keys its chevrons on carries no state until its script runs; the panel after it does.
    expect($open)
        ->toMatch('#<ui-disclosure class="[^"]*\bsidebar-fold\b[^"]*"[^>]*\sopen\s#')
        ->not->toMatch('#<button[^>]*data-open#')
        ->toMatch('#</button>\s*<div class="[^"]*\bdata-open:block\b[^"]*"\s+data-open\s*>#')
        ->and($closed)->toMatch('#<ui-disclosure class="[^"]*\bsidebar-fold\b#')->not->toMatch('#"\s+data-open\s*>#');

    $css = (string) str(File::get(__DIR__.'/../resources/css/foundry.css'))->after('@utility sidebar-fold {');

    expect($css)
        ->toContain("&:has(> [data-open]) > button > div:first-child > svg:first-child {\n        display: block;")
        ->toContain("&:has(> [data-open]) > button > div:first-child > svg:last-child {\n        display: none;");
});

test('the rail\'s footer is a nav of its own at the foot, over the account menu, named for its tools unless the slot names it', function () {
    $this->app->register(LivewireServiceProvider::class);
    $this->app->register(FluxServiceProvider::class);

    $html = Blade::render(<<<'BLADE'
        <foundry:app.sidebar :user="$user">
            <a id="a-link"></a>
            <x-slot:footer><a id="the-lab"></a></x-slot:footer>
        </foundry:app.sidebar>
        BLADE, ['user' => $this->user], deleteCachedView: true);

    $rail = (string) str($html)->before('</ui-sidebar>');
    $footer = (string) str($rail)->match('#<nav[^>]*aria-label="Tools"[^>]*>.*?</nav>#s');

    expect($footer)->toContain('<a id="the-lab"></a>')->toContain('border-t border-zinc-50/13 pt-4')->not->toContain('a-link')
        ->and(strpos($rail, 'data-flux-sidebar-spacer'))->toBeLessThan(strpos($rail, 'aria-label="Tools"'))
        ->and(strpos($rail, 'aria-label="Tools"'))->toBeLessThan(strpos($rail, 'data-flux-sidebar-profile'));

    $named = Blade::render('<foundry:app.sidebar><a></a><x-slot:footer aria-label="Lab"><a></a></x-slot:footer></foundry:app.sidebar>', deleteCachedView: true);
    $bare = Blade::render('<foundry:app.sidebar><a></a></foundry:app.sidebar>', deleteCachedView: true);

    expect($named)->toContain('aria-label="Lab"')->not->toContain('aria-label="Tools"')
        ->and(substr_count($bare, '<nav'))->toBe(1);
});

test('the current item draws its edge as an inset ring rather than Flux\'s border, so nothing in it moves', function () {
    $this->app->register(LivewireServiceProvider::class);
    $this->app->register(FluxServiceProvider::class);

    $item = (string) str(Blade::render('<foundry:app.sidebar.item href="/inbox" icon="inbox" count="12" :current="true">Inbox</foundry:app.sidebar.item>', deleteCachedView: true))->match('#<a [^>]*>#');

    expect($item)
        ->toContain('data-current:border ')
        ->toContain('data-current:border-0!')
        ->toContain('data-current:ring-1 data-current:ring-inset data-current:ring-white/10')
        ->toContain('data-current:before:bg-primary-300');
});

test('the rail\'s links follow with wire:navigate, and each can be turned to a full page load', function () {
    $this->app->register(LivewireServiceProvider::class);
    $this->app->register(FluxServiceProvider::class);

    $links = fn (string $blade): array => str(Blade::render($blade, deleteCachedView: true))->matchAll('#<a href="([^"]+)"[^>]*>#')->all();
    $tags = fn (string $blade): array => str(Blade::render($blade, deleteCachedView: true))->matchAll('#(<a href="[^"]+"[^>]*>)#')->all();

    $rail = $tags(<<<'BLADE'
        <foundry:app.sidebar home="/dashboard">
            <foundry:app.sidebar.item href="/inbox">Inbox</foundry:app.sidebar.item>
            <foundry:app.sidebar.group heading="Board"><foundry:app.sidebar.item href="/board/q3">Q3 update</foundry:app.sidebar.item></foundry:app.sidebar.group>
            <foundry:app.sidebar.item href="/export.csv" :navigate="false">Export</foundry:app.sidebar.item>
        </foundry:app.sidebar>
        BLADE);

    expect($links('<foundry:app.sidebar home="/dashboard"><foundry:app.sidebar.item href="/inbox">Inbox</foundry:app.sidebar.item></foundry:app.sidebar>'))->toBe(['/dashboard', '/inbox', '/dashboard']);

    foreach ($rail as $tag) {
        str_contains($tag, '/export.csv')
            ? expect($tag)->not->toContain('wire:navigate')
            : expect($tag)->toContain('wire:navigate');
    }

    expect(collect($tags('<foundry:app.sidebar home="/dashboard" :navigate="false"><a></a></foundry:app.sidebar>'))->filter(fn ($tag) => str_contains($tag, '/dashboard'))->all())
        ->toHaveCount(2)
        ->each->not->toContain('wire:navigate');
});

test('the rail keeps its offset: Livewire\'s across history, its own across links and reloads, before it paints, still followed by the bar Flux lays beside it', function () {
    $this->app->register(LivewireServiceProvider::class);
    $this->app->register(FluxServiceProvider::class);

    $html = Blade::render('<foundry:app.sidebar><a></a></foundry:app.sidebar>', deleteCachedView: true);

    expect($html)
        ->toMatch('#<script>\s*\(\(\) => \{\s*const rail = document\.currentScript\.parentElement;.*?</script>\s*</ui-sidebar>#s')
        ->toMatch('#<ui-sidebar[^>]*\swire:navigate:scroll(="")?[\s>]#')
        ->toMatch('#<ui-sidebar[^>]*\sdata-scroll-id="foundry-rail"#')
        ->toContain("rail.scrollTop = Number(rail.dataset.scrollY ?? sessionStorage.getItem('foundry-sidebar-scroll')) || 0;")
        ->toContain("sessionStorage.setItem('foundry-sidebar-scroll', rail.scrollTop);")
        ->toContain('{ passive: true }')
        ->toMatch('#</ui-sidebar>\s*<header[^>]*data-flux-header#');
});
