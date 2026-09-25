<?php

namespace Steddle\Foundry\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Where `bin/shoot --as` signs an account in, whether the imprint signs in
 * by link or through the Steddle account. Local only, and signed.
 */
final class SignInToShoot
{
    public function __invoke(Request $request, string $user): RedirectResponse
    {
        Auth::loginUsingId($user, remember: true);
        $request->session()->regenerate();

        return redirect('/');
    }
}
