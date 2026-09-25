<?php

namespace Steddle\Foundry\Http\Controllers;

use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Steddle\Foundry\Auth\SteddleProvider;
use Steddle\Foundry\Locales;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirect;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Sign-in through the Steddle account. Signing out here is this imprint's
 * alone; signing out everywhere is the account's, on its server.
 */
final class AccountController
{
    public function redirect(Request $request): SymfonyRedirect
    {
        return $this->provider()->with(array_filter([
            'ui_locales' => app()->getLocale(),
            'login_hint' => $request->string('email')->value(),
        ]))->redirect();
    }

    public function callback(Request $request): RedirectResponse|View
    {
        // The reader turned the consent down: back where a guest may be, not to an error.
        if ($request->query('error') === 'access_denied') {
            $intended = $request->session()->pull('url.intended');

            return redirect()->to($this->openToGuests($intended) ? $intended : (Locales::multilingual() ? localized_route('home') : url('/')));
        }

        if ($request->has('error')) {
            return view('foundry::account.error');
        }

        try {
            $account = $this->provider()->user();
        } catch (InvalidStateException|HttpClientException) {
            return view('foundry::account.error');
        }

        $user = config('auth.providers.users.model')::fromSteddleAccount($account->getRaw());

        Auth::login($user, remember: true);
        $request->session()->regenerate();
        $request->session()->put('credential', ['kind' => $account->credential['kind'], 'link_id' => $account->credential['id']]);

        return redirect()->intended(url(config('imprint.account.home') ?? '/'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /** A page behind `auth` would send a guest straight back to sign in, and so to the consent they turned down. */
    private function openToGuests(?string $url): bool
    {
        if ($url === null) {
            return false;
        }

        try {
            $route = Route::getRoutes()->match(Request::create($url));
        } catch (HttpExceptionInterface) {
            return false;
        }

        return ! collect(app('router')->gatherRouteMiddleware($route))
            ->contains(fn (mixed $middleware): bool => is_string($middleware) && is_a(Str::before($middleware, ':'), AuthenticatesRequests::class, true));
    }

    /** Built per request: the manager keeps a driver it made, with the request and parameters it was made with. */
    private function provider(): SteddleProvider
    {
        return Socialite::buildProvider(SteddleProvider::class, [
            'client_id' => config('imprint.account.client'),
            'client_secret' => config('imprint.account.secret'),
            'redirect' => route('foundry.account.callback'),
        ]);
    }
}
