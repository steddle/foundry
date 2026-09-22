<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Livewire\Component;
use Livewire\Livewire;
use Livewire\LivewireServiceProvider;
use Steddle\Foundry\Concerns\HasInitials;

beforeEach(function () {
    $this->artisan('view:clear');
    $directory = resource_path('views/components/site');
    File::ensureDirectoryExists($directory);
    File::put($directory.'/lockup.blade.php', '<span {{ $attributes }}>Imprint</span>');
    File::put($directory.'/mark.blade.php', '<svg {{ $attributes }}></svg>');
    config(['imprint.name' => 'Imprint', 'imprint.stylesheet' => null]);
    $this->user = (object) ['name' => 'Ada Visser', 'email' => 'ada@example.com'];
});

afterEach(function () {
    File::deleteDirectory(resource_path('views/components'));
});

test('a signed-in page is a noindex document with its links, the current one marked, the account menu and the toasts', function () {
    Route::post('logout', fn () => null)->name('logout');
    Route::getRoutes()->refreshNameLookups();
    url()->setRequest(Request::create('/agreements/12'));

    $html = Blade::render(
        '<x-site.app-page title="Agreements" :links="$links" :user="$user"><x-slot:menu><flux:menu.item href="/settings">Settings</flux:menu.item></x-slot:menu><p>Body</p></x-site.app-page>',
        ['links' => ['Agreements' => url('/agreements'), 'Settings' => url('/settings')], 'user' => $this->user],
        deleteCachedView: true,
    );

    expect($html)
        ->toContain('>Agreements | Imprint</title>')
        ->toContain('<meta name="robots" content="noindex" />')
        ->toMatch('#<a href="'.preg_quote(url('/agreements')).'"\s+aria-current="page"#')
        ->not->toMatch('#<a href="'.preg_quote(url('/settings')).'"\s+aria-current="page"#')
        ->toContain('aria-label="Account menu for Ada Visser"')
        ->toContain('<flux:avatar as="button"')
        ->toContain('ada@example.com')
        ->toContain('href="/settings"')
        ->toContain('action="'.url('/logout').'"')
        ->toContain('name="_token"')
        ->toContain('Log out')
        ->toContain('<flux:toast.group>')
        ->toContain('<p>Body</p>');
});

test('the account menu leaves out logging out where the imprint has no logout route', function () {
    $html = Blade::render('<x-site.account-menu :user="$user" />', ['user' => $this->user], deleteCachedView: true);

    expect($html)->toContain('Ada Visser')->not->toContain('Log out');
});

test('an ink page sets its toast group through x-site.toasts', function () {
    $html = Blade::render('<x-site.ink-page title="Sign in" description="Sign in." card>Form</x-site.ink-page>', deleteCachedView: true);

    expect($html)->toContain('<flux:toast.group>')->toContain('Form');
});

test('a record row keeps its actions outside its link', function () {
    $html = Blade::render('<x-site.record-row href="/agreements/12" title="Mutual NDA" meta="Sent 21 Sep 2026"><x-slot:status><span>Signed</span></x-slot:status><x-slot:actions><button>Resend</button></x-slot:actions></x-site.record-row>', deleteCachedView: true);

    $link = str($html)->after('<a href="/agreements/12"')->before('</a>');

    expect((string) $link)->toContain('Mutual NDA')->toContain('Sent 21 Sep 2026')->toContain('Signed')->not->toContain('Resend')
        ->and((string) str($html)->after('</a>'))->toContain('<button>Resend</button>');
});

test('a page head sets the trail above it, the status beside the title and the actions', function () {
    $html = Blade::render('<x-site.page-head :breadcrumbs="[\'Agreements\' => \'/agreements\']" title="Mutual NDA" lead="Sent today."><x-slot:status><span>Signed</span></x-slot:status><x-slot:actions><a href="/pdf">Download</a></x-slot:actions></x-site.page-head>', deleteCachedView: true);

    expect($html)
        ->toContain('<flux:breadcrumbs data-markdown-skip')
        ->toContain('>Agreements</flux:breadcrumbs.item>')
        ->toContain('Mutual NDA')
        ->toContain('<span>Signed</span>')
        ->toContain('Sent today.')
        ->toContain('<a href="/pdf">Download</a>');
});

test('a page head leads back to the one page above it', function () {
    $html = Blade::render('<x-site.page-head :back="[\'All agreements\' => \'/agreements\']" title="Mutual NDA" />', deleteCachedView: true);

    expect($html)
        ->toContain('<a href="/agreements"')
        ->toContain('<flux:icon.arrow-left variant="micro" />All agreements')
        ->not->toContain('<flux:breadcrumbs');
});

test('a field row links its label to its control', function () {
    $html = Blade::render('<x-site.field-row label="Email" description="Where links go." for="email"><input id="email" /></x-site.field-row>', deleteCachedView: true);

    expect($html)->toContain('<label for="email"')->toContain('<input id="email" />')->toContain('Where links go.');
});

test('a confirm item asks twice in the menu and acts on the third press', function () {
    $html = Blade::render('<x-site.confirm-item icon="trash" action="$wire.delete(\'abc\')">Delete</x-site.confirm-item>', deleteCachedView: true);

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
    $html = Blade::render('<x-site.confirm-button label="Delete account" action="$wire.deleteAccount()" />', deleteCachedView: true);

    expect($html)
        ->toContain('aria-label="Delete account"')
        ->toContain('step = 3; timer = setTimeout(() => Promise.resolve().then(() => $wire.deleteAccount())')
        ->toContain('<flux:icon.check variant="micro" />Done')
        ->toContain('clip-path: inset(0 ${100 - (Math.min(step, 3) / 3) * 100}% 0 0)')
        ->toContain('One more time')
        ->not->toContain('wire:click');
});

test('a badge draws a hairline ring in its own ramp', function () {
    expect(Blade::render('<x-site.badge tone="success">Signed</x-site.badge>', deleteCachedView: true))
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

test('a flush app page lays its bar over the band and sets its slot edge to edge', function () {
    $this->artisan('view:clear');
    File::ensureDirectoryExists(resource_path('views/components/site'));
    File::put(resource_path('views/components/site/lockup.blade.php'), '<span {{ $attributes }}>Imprint</span>');

    try {
        $user = (object) ['name' => 'Ada Visser', 'email' => 'ada@example.com', 'initials' => 'AV'];
        $flush = Blade::render('<x-site.app-page title="x" :user="$user" flush><x-site.app-band><p>Band</p></x-site.app-band></x-site.app-page>', ['user' => $user], deleteCachedView: true);
        $contained = Blade::render('<x-site.app-page title="x" :user="$user"><p>Body</p></x-site.app-page>', ['user' => $user], deleteCachedView: true);
    } finally {
        File::deleteDirectory(resource_path('views/components'));
    }

    $main = fn (string $html): string => (string) str($html)->between('<main class="flex-1">', '</main>');

    expect($flush)->toContain('<header class="absolute inset-x-0 top-0 z-10 ink">')
        ->and($contained)->toContain('<header class="border-b')
        ->and($main($flush))->toContain('bg-zinc-900 ink grain')->toContain('<p>Band</p>')->not->toContain('py-10')
        ->and($main($contained))->toContain('py-10')->toContain('<p>Body</p>');
});

test('a confirm button tells a screen reader each step, and a keyboard press starts no timer', function () {
    $html = Blade::render('<x-site.confirm-button label="Delete account" action="$wire.deleteAccount()" />', deleteCachedView: true);

    expect($html)
        ->toMatch('#</flux:button>\s*<span role="status" class="sr-only"#')
        ->toContain('Press again to confirm')
        ->toContain('if ($event.detail > 0) timer = setTimeout(() => step = 0, 1500)')
        ->toContain('x-on:blur="if (step < 3) { clearTimeout(timer); step = 0 }"');
});

test('a confirm item tells a screen reader each step, and starts over when focus leaves it', function () {
    $html = Blade::render('<x-site.confirm-item icon="trash" action="$wire.delete(\'abc\')">Delete</x-site.confirm-item>', deleteCachedView: true);

    expect($html)
        ->toMatch('#</flux:menu.item>\s*<span role="status" class="sr-only"#')
        ->toContain('Press again to confirm')
        ->toContain('if ($event.detail > 0) timer = setTimeout(() => step = 0, 1500)')
        ->toContain('x-on:focusout="if (step < 3) { clearTimeout(timer); step = 0 }"');
});

test('the app bar\'s panel takes an id of its own and hands focus back on escape', function () {
    $html = Blade::render('<x-site.app-nav :links="[\'Agreements\' => \'/agreements\']" :user="$user" />', ['user' => $this->user], deleteCachedView: true);

    expect($html)
        ->toContain('x-id="[\'app-menu\']"')
        ->toContain(':aria-controls="$id(\'app-menu\')"')
        ->toContain(':id="$id(\'app-menu\')"')
        ->toContain('x-ref="toggle"')
        ->toContain('$el.contains(document.activeElement) && $refs.toggle.focus()')
        ->not->toContain('id="app-menu"');
});

test('the app bar names its navigation in the page\'s language', function () {
    app()->setLocale('nl');

    $html = Blade::render('<x-site.app-nav :user="$user" />', ['user' => $this->user], deleteCachedView: true);

    expect($html)->toContain('<nav aria-label="Hoofdmenu"');
});

test('a signed-in page loads Livewire, Alpine with it, once, with a component on it or without', function (string $content) {
    $this->app->register(LivewireServiceProvider::class);
    Livewire::component('shell-probe', ShellProbe::class);
    Route::get('/shell', fn () => Blade::render('<x-site.app-page title="Shell" :user="$user">'.$content.'</x-site.app-page>', ['user' => $this->user], deleteCachedView: true));

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
