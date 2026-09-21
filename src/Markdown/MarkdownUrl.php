<?php

namespace Steddle\Foundry\Markdown;

final class MarkdownUrl
{
    /**
     * The address a page's markdown answers on: `/index.md` for a root, which
     * RewriteMarkdownUrls reads back, and the path with `.md` otherwise.
     */
    public static function of(string $url): string
    {
        if (in_array(parse_url($url, PHP_URL_PATH), [null, '', '/'], true)) {
            return rtrim($url, '/').'/index.md';
        }

        return rtrim($url, '/').'.md';
    }
}
