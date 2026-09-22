<?php

use Illuminate\Auth\GenericUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\McpServiceProvider;
use Steddle\Foundry\Contracts\Sitemap;
use Steddle\Foundry\Mcp\ReadDocs;

final class DocsServer extends Server
{
    protected array $tools = [ReadDocs::class];
}

final class BilingualPages implements Sitemap
{
    public function pages(): array
    {
        $en = app()->getLocale() === 'en';

        return [
            'home' => ['title' => $en ? 'Home' : 'Start', 'description' => 'The imprint.', 'url' => url($en ? '/en' : '/'), 'render' => fn (): string => '<h1>'.(app()->getLocale() === 'en' ? 'Home' : 'Start').'</h1>'],
            'docs' => ['title' => 'Docs', 'description' => 'How it works.', 'url' => url($en ? '/en/docs' : '/docs'), 'render' => fn (): string => '<h1>Docs</h1><p>Rendered in '.app()->getLocale().'.</p>'],
            'search' => ['title' => 'Search', 'description' => 'Ask.', 'url' => url($en ? '/en/search' : '/zoeken'), 'render' => null],
        ];
    }

    public function sections(): array
    {
        return [];
    }
}

final class GuardedPages implements Sitemap
{
    public function pages(): array
    {
        return ['start' => ['title' => 'Start', 'description' => 'Begin.', 'url' => url('/en/start'), 'render' => fn (): string => '<p>'.(auth()->check() ? 'Your NDAs' : 'Sign in').'</p>']];
    }

    public function sections(): array
    {
        return [];
    }
}

beforeEach(function () {
    $this->app->register(McpServiceProvider::class);
    config()->set('imprint.name', 'Imprint');
    config()->set('imprint.sitemap', BilingualPages::class);
    config()->set('imprint.locales', ['nl' => ['name' => 'Nederlands'], 'en' => ['name' => 'English']]);
    config()->set('imprint.docs.description', 'How the imprint works.');
    app()->setLocale('nl');
});

test('without a path it lists the English pages, with their paths', function () {
    DocsServer::tool(ReadDocs::class)
        ->assertOk()
        ->assertSee(['"path":"/en"', '"path":"/en/docs"', '"path":"/en/search"', '"title":"Home"'])
        ->assertDontSee(['"path":"/docs"', '"title":"Start"']);
});

test('a page reads as markdown in English, by path, .md twin or full address', function (string $address) {
    DocsServer::tool(ReadDocs::class, ['path' => $address])
        ->assertOk()
        ->assertSee(['Docs', 'Rendered in en.'])
        ->assertDontSee('<p>');
})->with(['/en/docs', 'en/docs.md', 'http://localhost/en/docs']);

test('the English home reads at its markdown address', function () {
    DocsServer::tool(ReadDocs::class, ['path' => '/en.md'])->assertOk()->assertSee('Home');
});

test('a page that does not render, or no page at all, answers with the way to the list', function (string $path) {
    DocsServer::tool(ReadDocs::class, ['path' => $path])
        ->assertHasErrors(["No page at {$path}. Call read-docs without a path to list every page."]);
})->with(['/en/search', '/docs']);

test('the description names the imprint and says what its docs cover', function () {
    DocsServer::tool(ReadDocs::class)
        ->assertName('read-docs')
        ->assertDescription('Read Imprint\'s public pages as markdown, in English: the docs, the legal documents and every other page it publishes. How the imprint works. Without a path it lists every page with its path; with one it returns that page. Quote these when the person asks what a term means, rather than paraphrasing.');
});

test('a signed-in reader and a guest are cached apart, so neither reads the other\'s links', function () {
    config()->set('imprint.sitemap', GuardedPages::class);

    DocsServer::tool(ReadDocs::class, ['path' => '/en/start'])->assertOk()->assertSee('Sign in');
    DocsServer::actingAs(new GenericUser(['id' => 1]))->tool(ReadDocs::class, ['path' => '/en/start'])->assertOk()->assertSee('Your NDAs');

    Auth::forgetGuards();

    DocsServer::tool(ReadDocs::class, ['path' => '/en/start'])->assertOk()->assertSee('Sign in');
});

test('nothing is cached when markdown-response.cache.enabled is off', function () {
    config()->set('markdown-response.cache.enabled', false);

    DocsServer::tool(ReadDocs::class, ['path' => '/en/docs'])->assertOk();

    expect(Cache::store(config('markdown-response.cache.store'))->has('read-docs:/en/docs:guest'))->toBeFalse();
});
