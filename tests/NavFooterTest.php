<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->artisan('view:clear');
    $directory = resource_path('views/foundry');
    File::ensureDirectoryExists($directory);
    File::put($directory.'/lockup.blade.php', '@props([\'endorsed\' => false])<span {{ $attributes }}>@if ($endorsed)Imprint by Steddle @else Imprint @endif</span>');
    File::put($directory.'/scene.blade.php', '@props([\'name\', \'scrim\'])<img data-scene="{{ $name }}" {{ $attributes }}><div class="{{ $scrim }}"></div>');
    File::put($directory.'/text.blade.php', '<p {{ $attributes }}>{{ $slot }}</p>');
    config()->set('imprint.name', 'Imprint');
});

afterEach(function () {
    File::deleteDirectory(resource_path('views/components'));
    File::deleteDirectory(resource_path('views/foundry'));
});

test('the bar folds its links and actions into a phone menu', function () {
    $html = Blade::render('<foundry:header :links="[\'Docs\' => \'/docs\']" menu-label="Menü"><x-slot:actions><a href="/login">Log in</a></x-slot:actions></foundry:header>', deleteCachedView: true);

    expect($html)
        ->toContain('aria-label="Imprint, home"')
        ->toContain('aria-label="Menü"')
        ->toContain('x-id="[\'site-menu\']"')
        ->toContain(':aria-controls="$id(\'site-menu\')"')
        ->toContain(':id="$id(\'site-menu\')"')
        ->toContain('$el.contains(document.activeElement) && $refs.toggle.focus()')
        ->and(substr_count($html, 'href="/docs"'))->toBe(2)
        ->and(substr_count($html, 'href="/login"'))->toBe(2);
});

test('without a menu the lockup and the actions are the whole bar on a phone', function () {
    $html = Blade::render('<foundry:header :menu="false"><x-slot:actions><a href="mailto:hello@example.com">Talk to us</a></x-slot:actions></foundry:header>', deleteCachedView: true);

    expect($html)
        ->not->toContain('x-data')
        ->not->toContain('gap-7 max-lg:hidden')
        ->and(substr_count($html, 'Talk to us'))->toBe(1);
});

test('the footer lays a flat scrim where no closing section is set, and the measured one and a rule below it where it is', function () {
    $bare = Blade::render('<foundry:footer scene="stones" />', deleteCachedView: true);
    $closed = Blade::render('<foundry:footer scene="stones"><section>Closing</section></foundry:footer>', deleteCachedView: true);

    expect($bare)->toContain('class="bg-zinc-900/90"')->toContain('data-scene="stones"')->not->toContain('<div class="border-t border-zinc-50/13"></div>')
        ->and($closed)->toContain('class="bg-[linear-gradient(')->toContain('<section>Closing</section>')->toContain('<div class="border-t border-zinc-50/13"></div>');
});

test('the footer lists its links, or the items a site sets, above the service line, endorsed unless the imprint says otherwise', function () {
    config()->set('imprint.disclaimer', 'Imprint is not a law firm.');
    $html = Blade::render('<foundry:footer scene="p" :links="[\'Legal\' => \'/legal\']" />', deleteCachedView: true);
    config()->set('imprint.endorsed', false);
    $items = Blade::render('<foundry:footer scene="p"><x-slot:items><li>Services</li></x-slot:items></foundry:footer>', deleteCachedView: true);

    expect($html)->toContain('<a href="/legal" class="hover:text-zinc-950 dark:hover:text-zinc-50">Legal</a>')
        ->toContain('Imprint is not a law firm.')
        ->toContain('© '.now()->year.' Imprint')
        ->toContain('A service by')
        ->toContain('Imprint by Steddle')
        ->and($items)->toContain('<li>Services</li>')->not->toContain('by Steddle')->not->toContain('A service by');
});

test('the site bar closes on a signed-in reader\'s account menu, with the imprint\'s items, at every width', function () {
    $html = Blade::render('<foundry:header :links="[\'Docs\' => \'/docs\']" :user="$user"><x-slot:account><flux:menu.item href="/settings">Settings</flux:menu.item></x-slot:account></foundry:header>', ['user' => (object) ['name' => 'Ada Visser', 'email' => 'ada@example.com']], deleteCachedView: true);

    expect($html)->toContain('<flux:avatar')->toContain('href="/settings">Settings');

    expect(Blade::render('<foundry:header :links="[\'Docs\' => \'/docs\']" />', deleteCachedView: true))->not->toContain('<flux:avatar');
});

test('a page takes the imprint\'s bar and the site\'s footer', function () {
    File::put(resource_path('views/foundry/head.blade.php'), '<title>{{ $title }}</title>');
    File::put(resource_path('views/foundry/bar.blade.php'), '<nav id="the-site-bar"></nav>');

    $own = Blade::render(<<<'BLADE'
        <foundry:layouts.site title="Home" description="d">
            <p>Body</p>
            <x-slot:footer><footer id="the-site-footer"></footer></x-slot:footer>
        </foundry:layouts.site>
    BLADE, deleteCachedView: true);

    expect($own)->toContain('id="the-site-bar"')->toContain('id="the-site-footer"')->not->toContain('<header');
});

test('without a bar and a footer of the imprint\'s own a page keeps the foundry\'s', function () {
    File::put(resource_path('views/foundry/head.blade.php'), '<title>{{ $title }}</title>');

    expect(Blade::render('<foundry:layouts.site title="Home" description="d"><p>Body</p></foundry:layouts.site>', deleteCachedView: true))
        ->toContain('<header')
        ->toContain('<footer');
});

test('the bar marks the link whose address the current one equals or lies under, and a root link only on the root', function () {
    url()->setRequest(Request::create('/agreements/12'));

    $html = Blade::render('<foundry:header :links="$links" />', ['links' => ['Home' => url('/'), 'Agreements' => url('/agreements'), 'Settings' => url('/settings')]], deleteCachedView: true);

    expect($html)
        ->toMatch('#<a href="'.preg_quote(url('/agreements')).'"\s+aria-current="page"#')
        ->not->toMatch('#<a href="'.preg_quote(url('/settings')).'"\s+aria-current="page"#')
        ->not->toMatch('#<a href="'.preg_quote(url('/')).'"\s+aria-current="page"#');
});

test('the bar names its navigation and its menu in the page\'s language', function () {
    app()->setLocale('nl');

    expect(Blade::render('<foundry:header :links="[\'Docs\' => \'/docs\']" />', deleteCachedView: true))
        ->toContain('<nav aria-label="Hoofdmenu"')
        ->toContain('aria-label="Menu"');
});
