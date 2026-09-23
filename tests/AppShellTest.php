<?php

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
        ->toContain('Log out');
});

test('the account menu leaves out logging out where the imprint has no logout route', function () {
    $html = Blade::render('<foundry:account-menu :user="$user" />', ['user' => $this->user], deleteCachedView: true);

    expect($html)->toContain('Ada Visser')->not->toContain('Log out');
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
