<?php

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;

final class ImprintComponents
{
    public static function all(): array
    {
        return ['stamp' => [
            'name' => 'Stamp',
            'group' => 'Signing',
            'description' => 'The imprint\'s own stamp.',
            'examples' => [['title' => 'Alone', 'blade' => '<x-site.stamp />']],
        ]];
    }
}

beforeEach(function () {
    $this->artisan('view:clear');
    File::ensureDirectoryExists(resource_path('views/components/site'));
    File::ensureDirectoryExists(resource_path('views/layouts'));
    File::put(resource_path('views/components/site/heading.blade.php'), '<h2 {{ $attributes }}>{{ $slot }}</h2>');
    File::put(resource_path('views/components/site/text.blade.php'), '<p {{ $attributes }}>{{ $slot }}</p>');
    File::put(resource_path('views/components/site/stamp.blade.php'), '<span>the stamp</span>');
    File::put(resource_path('views/layouts/site.blade.php'), '<main>{{ $slot }}</main>');
    Blade::anonymousComponentPath(resource_path('views/layouts'), 'layouts');
    config()->set('imprint.components', ImprintComponents::class);
});

afterEach(function () {
    File::deleteDirectory(resource_path('views/components'));
    File::deleteDirectory(resource_path('views/layouts'));
});

test('an imprint\'s own components join the foundry\'s, marked as its own', function () {
    $this->get('/components/stamp')->assertOk()
        ->assertSee('the stamp', false)
        ->assertSee('outside steddle/foundry')
        ->assertSee('href="'.route('foundry.components', 'hero').'"', false)
        ->assertSee('Custom components');
});

test('the custom filter lists the imprint\'s components alone, and the lab links to it', function () {
    $this->get('/components?custom=1')->assertOk()
        ->assertSee('href="'.route('foundry.components', ['stamp', 'custom' => 1]).'"', false)
        ->assertDontSee('href="'.route('foundry.components', ['hero', 'custom' => 1]).'"', false)
        ->assertSee('All components');

    $this->get('/components/hero?custom=1')->assertNotFound();

    $this->get('/labs')->assertSee('href="'.route('foundry.components', ['custom' => 1]).'"', false);
});

test('without custom components there is no filter', function () {
    config()->set('imprint.components', null);

    $this->get('/components/container')->assertOk()->assertDontSee('Custom components');
    $this->get('/components?custom=1')->assertNotFound();
});
