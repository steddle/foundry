<?php

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->artisan('view:clear');
    File::ensureDirectoryExists(resource_path('views/components'));
    File::ensureDirectoryExists(resource_path('views/foundry'));
    File::ensureDirectoryExists(resource_path('views/layouts'));
    File::put(resource_path('views/foundry/heading.blade.php'), '<h2 {{ $attributes }}>{{ $slot }}</h2>');
    File::put(resource_path('views/foundry/text.blade.php'), '<p {{ $attributes }}>{{ $slot }}</p>');
    File::put(resource_path('views/foundry/lockup.blade.php'), '<span {{ $attributes }}>Imprint</span>');
    File::put(resource_path('views/layouts/site.blade.php'), '<foundry:header /><main>{{ $slot }}</main>');
    Blade::anonymousComponentPath(resource_path('views/layouts'), 'layouts');
    config()->set('imprint.name', 'Imprint');
});

afterEach(function () {
    File::deleteDirectory(resource_path('views/components'));
    File::deleteDirectory(resource_path('views/foundry'));
    File::deleteDirectory(resource_path('views/layouts'));
    File::deleteDirectory(resource_path('views/labs'));
});

test('the lab lists the design page, the components and the imprint\'s experiments under its own bar', function () {
    config()->set('imprint.labs', ['changes' => ['Changes', 'Three ways to fill a row.']]);

    $this->get('/labs')->assertOk()
        ->assertSee('aria-label="Lab"', false)
        ->assertSee('href="'.route('foundry.design').'"', false)
        ->assertSee('href="'.route('foundry.components').'"', false)
        ->assertSee('href="'.route('foundry.lab', 'changes').'"', false)
        ->assertSee('Three ways to fill a row.');
});

test('an experiment is the imprint\'s own view, and one it does not hold is not found', function () {
    File::ensureDirectoryExists(resource_path('views/labs'));
    File::put(resource_path('views/labs/changes.blade.php'), 'The changes experiment.');

    $this->get('/labs/changes')->assertOk()->assertSee('The changes experiment.');
    $this->get('/labs/nothing')->assertNotFound();
});

test('outside the lab the bar ends on a link to it', function () {
    $html = Blade::render('<foundry:header :links="[\'Docs\' => \'/docs\']" />', deleteCachedView: true);

    expect($html)->toContain('aria-label="Main"')
        ->toContain('>Docs</a>')
        ->toContain('href="'.route('foundry.lab').'"');
});
