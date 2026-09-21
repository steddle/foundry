<?php

namespace Steddle\Foundry\Http\Middleware;

use Closure;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Marks a response for exclusion from a search index. The header form, not a
 * meta tag, so a route that answers something other than HTML still carries
 * it. `auth` and `can` answer an unmet guard by throwing rather than
 * returning, so the redirect (or the 403) that becomes has to be built the
 * same way the kernel itself would build it, or it never passes back through
 * here to be marked. Reporting it first keeps a bug on one of these routes
 * as visible to error tracking as it would be anywhere else the kernel
 * catches it.
 */
class Noindex
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $response = $next($request);
        } catch (Throwable $exception) {
            $handler = app(ExceptionHandler::class);
            $handler->report($exception);
            $response = $handler->render($request, $exception);
        }

        $response->headers->set('X-Robots-Tag', 'noindex');

        return $response;
    }
}
