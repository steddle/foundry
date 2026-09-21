<?php

use Steddle\Foundry\Contracts\Sitemap;

final class TestPages implements Sitemap
{
    public function pages(): array
    {
        return [
            'home' => ['title' => 'Home', 'description' => 'What the imprint is.', 'url' => url('/'), 'render' => fn (): string => '<h1>Home</h1><p>Words.</p>'],
            'search' => ['title' => 'Search', 'description' => 'Ask.', 'url' => url('/search'), 'render' => null],
        ];
    }

    public function sections(): array
    {
        return ['MCP' => ['- [MCP server](https://example.test/mcp)']];
    }
}

beforeEach(function () {
    config()->set('imprint.name', 'Imprint');
    config()->set('imprint.sitemap', TestPages::class);
    config()->set('markdown-response.cache.store', 'array');
    require __DIR__.'/../routes/foundry.php';
    app('router')->getRoutes()->refreshNameLookups();
});

test('llms.txt names the imprint, its pages and its own sections', function () {
    $this->get('/llms.txt')->assertOk()
        ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
        ->assertSee("# Imprint\n\n> What the imprint is.", false)
        ->assertSee('- [Search]('.url('/search.md').'): Ask.', false)
        ->assertSee("## MCP\n\n- [MCP server]", false);
});

test('llms-full.txt renders the pages that render, and the sitemap lists them all', function () {
    $this->get('/llms-full.txt')->assertOk()->assertSee('Words.')->assertDontSee('# '.url('/search'), false);
    $this->get('/sitemap.xml')->assertOk()->assertSee('<loc>'.url('/search').'</loc>', false)->assertDontSee('hreflang', false);
});
