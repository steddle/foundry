<?php

use Illuminate\Support\Facades\File;
use Livewire\LivewireServiceProvider;
use Steddle\Foundry\Tests\TestCase;

beforeAll(function () {
    TestCase::$providers = [LivewireServiceProvider::class];
    TestCase::$config = ['livewire.make_command' => ['type' => 'sfc', 'emoji' => true]];
});

afterAll(function () {
    TestCase::$providers = [];
    TestCase::$config = [];
});

test('make:livewire writes a folder with the class beside its view and no emoji, whatever the imprint\'s config says', function () {
    expect(config('livewire.make_command.type'))->toBe('mfc')
        ->and(config('livewire.make_command.emoji'))->toBeFalse();

    try {
        $this->artisan('make:livewire', ['name' => 'probe-card'])->assertSuccessful();

        $files = collect(File::allFiles(resource_path('views/components')))->map(fn ($file): string => $file->getRelativePathname())->sort()->values()->all();

        expect($files)->toBe(['probe-card/probe-card.blade.php', 'probe-card/probe-card.php']);
    } finally {
        File::deleteDirectory(resource_path('views/components'));
    }
});
