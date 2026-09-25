<?php

namespace Steddle\Foundry\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Steddle\Foundry\Locales;
use Symfony\Component\HttpFoundation\Response;

/**
 * A route that states its language with `locale:` runs after this and
 * overrules it.
 */
class FollowPreferredLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        App::setLocale(Locales::preferred($request));

        return $next($request);
    }
}
