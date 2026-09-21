<?php

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->artisan('view:clear');
    $directory = resource_path('views/components/site');
    File::ensureDirectoryExists($directory);
    File::put($directory.'/lockup.blade.php', '@props([\'endorsed\' => false])<span {{ $attributes }}>@if ($endorsed)Imprint by Steddle @else Imprint @endif</span>');
    File::put($directory.'/scene.blade.php', '@props([\'name\', \'scrim\'])<img data-scene="{{ $name }}" {{ $attributes }}><div class="{{ $scrim }}"></div>');
    config()->set('imprint.name', 'Imprint');
});

afterEach(function () {
    File::deleteDirectory(resource_path('views/components'));
});

test('the bar folds its links and actions into a phone menu', function () {
    $html = Blade::render('<x-site.nav :links="[\'Docs\' => \'/docs\']" menu-label="Menü"><x-slot:actions><a href="/login">Log in</a></x-slot:actions></x-site.nav>', deleteCachedView: true);

    expect($html)
        ->toContain('aria-label="Imprint, home"')
        ->toContain('aria-label="Menü"')
        ->toContain('id="mobile-menu"')
        ->and(substr_count($html, 'href="/docs"'))->toBe(2)
        ->and(substr_count($html, 'href="/login"'))->toBe(2);
});

test('without a menu the lockup and the actions are the whole bar on a phone', function () {
    $html = Blade::render('<x-site.nav :menu="false"><x-slot:actions><a href="mailto:hello@example.com">Talk to us</a></x-slot:actions></x-site.nav>', deleteCachedView: true);

    expect($html)
        ->not->toContain('x-data')
        ->not->toContain('max-lg:hidden">')
        ->and(substr_count($html, 'Talk to us'))->toBe(1);
});

test('the footer lays a flat scrim where no closing section is set, the measured one where it is', function () {
    $bare = Blade::render('<x-site.footer scene="stones" scrim="measured" flat-scrim="flat" />', deleteCachedView: true);
    $closed = Blade::render('<x-site.footer scene="stones" scrim="measured" flat-scrim="flat"><section>Closing</section></x-site.footer>', deleteCachedView: true);

    expect($bare)->toContain('class="flat"')->toContain('data-scene="stones"')
        ->and($closed)->toContain('class="measured"')->toContain('<section>Closing</section>');
});

test('the footer lists its links, or the items a site sets, above its colophon', function () {
    $html = Blade::render('<x-site.footer scene="p" scrim="s" flat-scrim="f" :links="[\'Legal\' => \'/legal\']"><x-slot:colophon><p>Not a law firm.</p></x-slot:colophon></x-site.footer>', deleteCachedView: true);
    $items = Blade::render('<x-site.footer scene="p" scrim="s" flat-scrim="f" :endorsed="false"><x-slot:items><li>Services</li></x-slot:items></x-site.footer>', deleteCachedView: true);

    expect($html)->toContain('<a href="/legal" class="hover:text-strong">Legal</a>')->toContain('<p>Not a law firm.</p>')->toContain('Imprint by Steddle')
        ->and($items)->toContain('<li>Services</li>')->not->toContain('by Steddle');
});
