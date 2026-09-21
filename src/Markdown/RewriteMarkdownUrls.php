<?php

namespace Steddle\Foundry\Markdown;

use Illuminate\Http\Request;
use Spatie\MarkdownResponse\Middleware\RewriteMarkdownUrls as BaseRewrite;

/**
 * The package's `.md` rewrite, plus the llms.txt convention for an address
 * without a file name: `/index.md` and `/docs/index.md` answer for `/` and
 * `/docs`, where the package would look for a route named `index`.
 */
final class RewriteMarkdownUrls extends BaseRewrite
{
    protected function rewriteUrlWithoutMdSuffix(Request $request): void
    {
        $path = preg_replace('/(?:\/index)?\.md$/', '', $request->getPathInfo());
        $queryString = $request->getQueryString();
        $uri = ($path === '' ? '/' : $path).($queryString ? "?{$queryString}" : '');

        $request->server->set('REQUEST_URI', $uri);

        $request->initialize(
            $request->query->all(),
            $request->request->all(),
            $request->attributes->all(),
            $request->cookies->all(),
            $request->files->all(),
            $request->server->all(),
            $request->getContent(),
        );
    }
}
