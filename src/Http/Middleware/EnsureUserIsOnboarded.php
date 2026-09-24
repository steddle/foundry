<?php

namespace Steddle\Foundry\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sends a signed-in account that has not named itself to /welcome, from
 * every route behind `auth` and from Passport's, where
 * FoundryServiceProvider sets it once `imprint.onboarding` is on. A request
 * without an account, or with one that does not onboard, passes, and so
 * does one with no session to come back through: a token's, an agent's
 * call to the imprint's API or MCP, or one that asks for JSON.
 */
final class EnsureUserIsOnboarded
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || ! $request->hasSession() || $request->expectsJson() || ! method_exists($user, 'hasOnboarded') || $user->hasOnboarded() || $request->routeIs('foundry.welcome', 'logout', '*livewire.update')) {
            return $next($request);
        }

        if ($request->isMethod('GET')) {
            $request->session()->put('url.intended', $request->fullUrl());
        }

        // What the sign-in flashed, a status or an analytics event, waits for the page after /welcome.
        $request->session()->reflash();

        return redirect()->route('foundry.welcome');
    }
}
