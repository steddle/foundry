<?php

use Illuminate\Support\Facades\File;
use Steddle\Foundry\Content;
use Steddle\Foundry\Outline;

beforeEach(function () {
    File::ensureDirectoryExists(resource_path('content'));
    File::ensureDirectoryExists(resource_path('views/docs/articles'));
    File::put(resource_path('content/docs.php'), "<?php return ['start' => ['title' => 'Start', 'icon' => 'rocket-launch', 'description' => 'D', 'articles' => ['written' => ['Written', 'D'], 'planned' => ['Planned', 'D']]], 'later' => ['title' => 'Later', 'icon' => 'x', 'description' => 'D', 'articles' => ['someday' => ['Someday', 'D']]]];");
    File::put(resource_path('views/docs/articles/written.blade.php'), '<h2>What it is</h2><p>Text.</p><h2 id="kept">Kept</h2>');
});

afterEach(function () {
    File::deleteDirectory(resource_path('content'));
    File::deleteDirectory(resource_path('views/docs'));
});

test('the docs publish an article only where its view exists, and a topic only with one', function () {
    expect(Content::docs())->toHaveKey('start')->not->toHaveKey('later')
        ->and(array_keys(Content::docs()['start']['articles']))->toBe(['written']);
});

test('a document\'s outline is its own h2s, each given an id from its words unless it has one', function () {
    $outline = Outline::of(view('docs.articles.written')->render());

    expect($outline['headings'])->toBe(['what-it-is' => 'What it is', 'kept' => 'Kept'])
        ->and($outline['html'])->toContain('<h2 id="what-it-is">What it is</h2>');
});

test('the docs are read once a request for each locale, not once for all', function () {
    File::ensureDirectoryExists(resource_path('content/nl'));
    File::put(resource_path('content/nl/docs.php'), "<?php return ['start' => ['title' => 'Begin', 'icon' => 'x', 'description' => 'D', 'articles' => ['written' => ['Geschreven', 'D']]]];");

    app()->setLocale('en');
    $english = Content::docs()['start']['title'];
    app()->setLocale('nl');

    expect($english)->toBe('Start')->and(Content::docs()['start']['title'])->toBe('Begin');
});
