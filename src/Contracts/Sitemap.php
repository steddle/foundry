<?php

namespace Steddle\Foundry\Contracts;

use Closure;

/**
 * The imprint's public pages, as `imprint.sitemap` names the class that lists
 * them. The foundry calls it once per locale, with that locale set.
 */
interface Sitemap
{
    /**
     * The pages in the order the sitemap and llms.txt list them. `render`
     * builds the page's HTML for llms-full.txt, or is null for a page a
     * controller answers.
     *
     * @return array<string, array{title: string, description: string, url: string, render: (Closure(): string)|null}>
     */
    public function pages(): array;

    /**
     * What llms.txt says beyond the pages, `heading => lines`.
     *
     * @return array<string, list<string>>
     */
    public function sections(): array;
}
