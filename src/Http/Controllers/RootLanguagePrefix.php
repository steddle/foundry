<?php

namespace Steddle\Foundry\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * The root language has no prefix, and an address that names it anyway means
 * the same page. Leading slashes, backslashes and whitespace go first:
 * `/nl//other.example` would otherwise be sent on as `//other.example`, an
 * address on another host.
 */
final class RootLanguagePrefix
{
    public function __invoke(Request $request, string $path = ''): RedirectResponse
    {
        $query = $request->getQueryString();

        // A control character anywhere would end the Location header early.
        $path = preg_replace('/[\x00-\x1F\x7F]/', '', $path);

        return redirect('/'.ltrim($path, '/\\ ').($query === null ? '' : "?{$query}"), 301);
    }
}
