<?php

namespace Steddle\Foundry\Mcp;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Localizable;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Spatie\MarkdownResponse\Facades\Markdown;
use Steddle\Foundry\Pages;

/**
 * The imprint's public pages as markdown, for an imprint's MCP server to
 * register. Always the English pages, whatever the imprint's root language:
 * the agent translates, and one language keeps one set of paths to cite.
 * Needs laravel/mcp, which the foundry only suggests.
 */
#[Name('read-docs')]
#[IsReadOnly]
#[IsIdempotent]
final class ReadDocs extends Tool
{
    use Localizable;

    private const LOCALE = 'en';

    public function description(): string
    {
        return 'Read '.config('imprint.name').'\'s public pages as markdown, in English: the docs, the legal documents and every other page it publishes. '
            .__(config('imprint.docs.description'), locale: self::LOCALE)
            .' Without a path it lists every page with its path; with one it returns that page. Quote these when the person asks what a term means, rather than paraphrasing.';
    }

    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'path' => ['sometimes', 'nullable', 'string', 'max:255'],
        ]);

        $pages = collect(app(Pages::class)->byLocale()[self::LOCALE])
            ->keyBy(fn (array $page): string => $this->pathOf($page['url']));

        if (blank($validated['path'] ?? null)) {
            return Response::json(['pages' => $pages->map(fn (array $page, string $path): array => [
                'path' => $path,
                'title' => $page['title'],
                'description' => $page['description'],
            ])->values()->all()]);
        }

        $path = $this->pathOf($validated['path']);
        $page = $pages->get($path);

        if ($page === null || $page['render'] === null) {
            return Response::error("No page at {$path}. Call {$this->name()} without a path to list every page.");
        }

        // The render closure reads the app's locale when it runs, so an imprint whose root is not English would wrap the page in its root language's chrome.
        $render = fn (): string => $this->withLocale(self::LOCALE, fn (): string => Markdown::convert(($page['render'])()));

        if (! config('markdown-response.cache.enabled', true)) {
            return Response::text($render());
        }

        // Keyed apart from a guest's, since a page's links may read auth() and a shared key would leak one caller's version to the other.
        return Response::text(Cache::store(config('markdown-response.cache.store'))->remember(
            'read-docs:'.$path.':'.($request->user() ? 'auth' : 'guest'),
            config('markdown-response.cache.ttl', 3600),
            $render,
        ));
    }

    /**
     * @return array<string, JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'path' => $schema->string()
                ->description('The page to read, as the list names it, e.g. "/docs" or "/legal/terms". Leave it out to list every page.'),
        ];
    }

    /**
     * A page's path as its address shows it, so a full URL, a path with or
     * without slashes and its .md twin all name the same page.
     */
    private function pathOf(string $address): string
    {
        $path = Str::of(parse_url($address, PHP_URL_PATH) ?? '')->trim('/')->chopEnd('.md');

        return '/'.($path->value() === 'index' ? '' : $path);
    }
}
