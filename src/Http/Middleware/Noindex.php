<?php

namespace Steddle\Foundry\Http\Middleware;

use Closure;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * The header, not a meta tag, so an answer that is not HTML carries it too.
 * `auth` and `can` throw on an unmet guard, so the redirect or 403 is built
 * here as the kernel would build it, or it never passes back through to be
 * marked. It is reported first, so error tracking still sees it.
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
