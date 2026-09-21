<?php

use Illuminate\Support\Facades\Route;
use Spatie\MarkdownResponse\Middleware\ProvideMarkdownResponse;
use Steddle\Foundry\Markdown\RemoveMarkdownSkipPreprocessor;

beforeEach(function () {
    Route::middleware(ProvideMarkdownResponse::class)->group(function () {
        Route::get('/', fn () => '<html><body><h1>Home</h1></body></html>');
        Route::get('/docs', fn () => '<html><body><table><tr><th>Kind</th><th>Count</th></tr><tr><td>artikel</td><td>2</td></tr></table></body></html>');
    });
});

test('the root answers markdown at /index.md', function () {
    $this->get('/index.md')
        ->assertOk()
        ->assertHeader('Content-Type', 'text/markdown; charset=UTF-8')
        ->assertSee("Home\n====", false);
});

test('a HEAD request never leaves an empty page in the markdown cache', function () {
    $this->head('/index.md');

    $this->get('/index.md')->assertSee("Home\n====", false);
});

test('a table reaches the markdown as a table', function () {
    $this->get('/docs.md')->assertSee('| artikel | 2 |', false);
});

test('a marked element goes whole, an Alpine arrow in its attributes and a nested twin included', function () {
    $html = '<p>keep</p><div data-markdown-skip x-data="{ f: () => 1 }"><div>inner</div>chrome</div><p>also keep</p>';

    expect((new RemoveMarkdownSkipPreprocessor)($html))->toBe('<p>keep</p><p>also keep</p>');
});

it('strips a marked element and everything it nests, keeping what stands outside it', function () {
    $html = '<p>before</p><div data-markdown-skip><span>Copy page</span></div><p>after</p>';

    expect((new RemoveMarkdownSkipPreprocessor)($html))->toBe('<p>before</p><p>after</p>');
});

it('reads a quoted attribute value whole, so a `>` inside it does not end the tag early', function () {
    // An Alpine x-data object literal holding an arrow function, as the copy
    // menu's does: `=>` sits inside the quoted attribute value.
    $html = '<div data-markdown-skip x-data="{ copy(k) { setTimeout(() => { this.copied = null }, 1500) } }"><span>Copy page</span></div><p>after</p>';

    expect((new RemoveMarkdownSkipPreprocessor)($html))->toBe('<p>after</p>');
});

it('finds data-markdown-skip after a quoted attribute holding `>`, not only before it', function () {
    $html = '<div x-data="{ a: 1 > 0 }" data-markdown-skip><span>Copy page</span></div><p>after</p>';

    expect((new RemoveMarkdownSkipPreprocessor)($html))->toBe('<p>after</p>');
});
