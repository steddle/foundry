<?php

namespace Steddle\Foundry\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Spatie\MarkdownResponse\Facades\Markdown;
use Steddle\Foundry\Locales;
use Steddle\Foundry\Markdown\MarkdownUrl;
use Steddle\Foundry\Pages;

/**
 * The sitemap, and the two files https://llmstxt.org/ names: an index an
 * agent reads first and the whole site's text in one file. All three read
 * `Pages`, so a page added there is listed everywhere.
 */
final class AgentFiles
{
    public function sitemap(): Response
    {
        $pages = Pages::byLocale();
        $urls = [];

        foreach ($pages[Locales::root()] as $key => $page) {
            $alternates = Locales::multilingual() ? collect($pages)->map(fn (array $locale): ?string => $locale[$key]['url'] ?? null)->filter()->all() : [];

            foreach (Locales::multilingual() ? $alternates : [Locales::root() => $page['url']] as $url) {
                $urls[] = ['loc' => $url, 'alternates' => $alternates];
            }
        }

        return response()
            ->view('foundry::sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function llms(): Response
    {
        $pages = Pages::byLocale();
        $lines = ['# '.config('imprint.name'), '', '> '.(array_values($pages[Locales::root()])[0]['description'] ?? ''), ''];

        foreach ($pages as $locale => $list) {
            $lines[] = '## '.(Locales::multilingual() ? config("imprint.locales.{$locale}.name") : 'Pages');
            $lines[] = '';

            foreach ($list as $page) {
                $lines[] = "- [{$page['title']}](".MarkdownUrl::of($page['url'])."): {$page['description']}";
            }

            $lines[] = '';
        }

        foreach (Pages::sections() as $heading => $sectionLines) {
            $lines = [...$lines, "## {$heading}", '', ...$sectionLines, ''];
        }

        array_push($lines, '## Full text', '', '- [llms-full.txt]('.route('llms.full').'): every page above, concatenated.');

        return $this->respond(implode("\n", $lines)."\n");
    }

    /**
     * Every page `Pages` can render, in every locale, as one file. The
     * markdown preprocessors strip whatever layout a page renders with, so
     * nav and footer never reach it.
     */
    public function full(): Response
    {
        $markdown = Cache::store(config('markdown-response.cache.store'))
            ->remember('llms-full.txt', config('markdown-response.cache.ttl', 3600), function (): string {
                $sections = [];

                foreach (Locales::all() as $locale) {
                    Pages::in($locale, function () use (&$sections): void {
                        foreach (config('imprint.pages')::all() as $page) {
                            if ($page['render'] !== null) {
                                $sections[] = '# '.$page['url']."\n\n".Markdown::convert(($page['render'])());
                            }
                        }
                    });
                }

                return implode("\n\n---\n\n", $sections)."\n";
            });

        return $this->respond($markdown);
    }

    /**
     * The headers `Spatie\MarkdownResponse` puts on every other markdown
     * response, so both files carry the same AI-training signal as a page's
     * own `.md` twin.
     */
    private function respond(string $body): Response
    {
        $signals = collect(config('markdown-response.content_signals', []))
            ->map(fn (string $value, string $key): string => "{$key}={$value}")
            ->implode(', ');

        return new Response($body, 200, array_filter([
            'Content-Type' => 'text/plain; charset=UTF-8',
            'X-Markdown-Tokens' => (string) (int) ceil(mb_strlen($body) / 4),
            'Content-Signal' => $signals !== '' ? $signals : null,
        ]));
    }
}
