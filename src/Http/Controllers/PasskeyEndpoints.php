<?php

namespace Steddle\Foundry\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

/**
 * `/.well-known/passkey-endpoints`, where a password manager finds the page
 * that adds and removes passkeys: the imprint's `settings`, where it has
 * one and passkey sign-in is routed.
 */
final class PasskeyEndpoints
{
    public function __invoke(): JsonResponse
    {
        abort_unless(Route::has('settings') && Route::has('passkey.login'), 404);

        return response()->json([
            'enroll' => route('settings'),
            'manage' => route('settings'),
        ]);
    }
}
