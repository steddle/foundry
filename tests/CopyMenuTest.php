<?php

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Js;
use Steddle\Foundry\Markdown\MarkdownUrl;

test('a page\'s markdown answers at its path with .md, and the root\'s at /index.md', function () {
    expect(MarkdownUrl::of('https://site.test'))->toBe('https://site.test/index.md')
        ->and(MarkdownUrl::of('https://site.test/'))->toBe('https://site.test/index.md')
        ->and(MarkdownUrl::of('https://site.test/docs/'))->toBe('https://site.test/docs.md')
        ->and(MarkdownUrl::of('https://site.test/docs/install'))->toBe('https://site.test/docs/install.md');
});

test('the copy menu offers the page\'s markdown and every page\'s, in the page\'s language', function () {
    app()->setLocale('nl');

    $html = Blade::render('<foundry:copy-menu page="https://site.test/docs.md" all="https://site.test/llms-full.txt" />', deleteCachedView: true);

    expect($html)->toContain('data-markdown-skip')
        ->toContain('href="https://site.test/docs.md"')
        ->toContain('href="https://site.test/llms-full.txt"')
        ->toContain('Pagina openen als Markdown')
        ->toContain((string) Js::from(__('foundry::agents.copy_all')));
});
