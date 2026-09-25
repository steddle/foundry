<?php

namespace Steddle\Foundry\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adds the imprint's icon to laravel/mcp's OAuth authorization server
 * metadata, where a connector draws it beside the sign-in, and its docs on
 * connecting where `imprint.mcp.docs` names them.
 */
final class BrandOAuthMetadata
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response instanceof JsonResponse) {
            $response->setData([
                ...$response->getData(true),
                'op_logo_uri' => asset('icon-512.png'),
                ...(config('imprint.mcp.docs') ? ['service_documentation' => url(config('imprint.mcp.docs'))] : []),
            ]);
        }

        return $response;
    }
}
