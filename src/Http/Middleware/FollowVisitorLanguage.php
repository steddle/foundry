<?php

namespace Steddle\Foundry\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Steddle\Foundry\Locales;
use Symfony\Component\HttpFoundation\Response;

/**
 * On root-language pages only: an address under a prefix is a choice
 * somebody made, and no header overrules it. A read and nothing else, because
 * a redirected POST loses its body. Both answers carry `Vary`, so nothing in
 * front caches one reader's language for the next.
 */
class FollowVisitorLanguage
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethodSafe()) {
            return $next($request);
        }

        $wanted = Locales::preferred($request);
        $counterpart = $wanted === Locales::root() ? null : Locales::counterpart($request, $wanted);

        $response = $counterpart === null ? $next($request) : redirect()->to($counterpart);
        $response->headers->set('Vary', implode(', ', array_unique([...$response->getVary(), 'Accept-Language', 'Cookie'])));

        return $response;
    }
}
