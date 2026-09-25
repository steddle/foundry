<?php

namespace Steddle\Foundry\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Steddle\Foundry\Auth\LoginLink;
use Steddle\Foundry\Auth\MagicLink;

/**
 * Sign-in by a link by email. With `imprint.auth.signup` the first link to an
 * address makes its account; without it only an existing account is mailed.
 * The answer is the same either way, so the form tells no one who has one.
 */
final class MagicLinkController
{
    public function send(Request $request): RedirectResponse
    {
        $email = Str::lower($request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
        ])['email']);

        /** @var class-string<Model> $model */
        $model = config('auth.providers.users.model');
        $user = $model::query()->where('email', $email)->first();

        if ($user === null && config('imprint.auth.signup')) {
            $user = (new $model)->forceFill(['name' => Str::before($email, '@'), 'email' => $email]);
            $user->save();
        }

        if ($user !== null) {
            // The page that sent the reader here travels on the row, so a link
            // opened in another browser still reaches it.
            $link = MagicLink::issue($user, intended: $request->session()->get('url.intended'));

            // Rendered on the queue, where the request's language and session are gone.
            $user->notify((new LoginLink($link->url, LoginLink::clientFor($request)['name'] ?? null))->locale(app()->getLocale()));
        }

        return back()->with('sign_in_email', $email);
    }

    /**
     * The link lands on a page that posts itself: a mail scanner that fetches
     * the link runs no script, so it cannot spend the token.
     */
    public function confirm(Request $request, string $user): View
    {
        $link = MagicLink::findByToken((string) $request->query('token'));

        return view('foundry::auth.confirm', [
            'usable' => $link !== null && (string) $link->user_id === $user && $link->isUsable(),
            'link' => $link,
            'switching' => Auth::check() && (string) Auth::id() !== $user,
            'action' => $request->fullUrl(),
        ]);
    }

    public function consume(Request $request, string $user): RedirectResponse
    {
        $link = MagicLink::consume((string) $request->query('token'), $user, $request->ip());

        if ($link === null) {
            return redirect()->to($request->fullUrl());
        }

        $account = $link->user;

        if (Auth::check() && (string) Auth::id() !== (string) $account->getKey()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        Auth::login($account, remember: true);
        $request->session()->regenerate();

        if (! $account->hasVerifiedEmail()) {
            $account->markEmailAsVerified();
        }

        // An invokable class of the imprint's own, handed the link and the request: a redirect for its purpose, or null.
        if (($redirect = config('imprint.auth.redirect')) && ($response = app($redirect)($link, $request))) {
            return $response;
        }

        $intended = $request->session()->pull('url.intended');

        return redirect()->to($link->intended ?? $intended ?? url(config('fortify.home', '/')));
    }
}
