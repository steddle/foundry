<?php

namespace Steddle\Foundry;

use Illuminate\Support\Traits\Localizable;
use Steddle\Foundry\Contracts\Sitemap;

/**
 * The imprint's public pages in every locale, from the `Sitemap` that
 * `imprint.sitemap` names.
 */
final class Pages
{
    use Localizable;

    /**
     * `locale => key => page`.
     *
     * @return array<string, array<string, array{title: string, description: string, url: string, render: (\Closure(): string)|null}>>
     */
    public function byLocale(): array
    {
        return collect(Locales::all())->mapWithKeys(fn (string $locale): array => [$locale => $this->withLocale($locale, fn (): array => $this->sitemap()->pages())])->all();
    }

    /**
     * Every page's rendered HTML, with its address, in every locale.
     *
     * @return list<array{url: string, html: string}>
     */
    public function rendered(): array
    {
        return collect(Locales::all())->flatMap(fn (string $locale): array => $this->withLocale($locale, fn (): array => collect($this->sitemap()->pages())
            ->filter(fn (array $page): bool => $page['render'] !== null)
            ->map(fn (array $page): array => ['url' => $page['url'], 'html' => ($page['render'])()])
            ->values()
            ->all()))->all();
    }

    /** @return array<string, list<string>> */
    public function sections(): array
    {
        return $this->sitemap()->sections();
    }

    private function sitemap(): Sitemap
    {
        return app(config('imprint.sitemap'));
    }
}
