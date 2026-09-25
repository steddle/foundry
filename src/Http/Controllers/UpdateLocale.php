<?php

namespace Steddle\Foundry\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Steddle\Foundry\Events\LocaleChosen;
use Steddle\Foundry\Locales;
use Symfony\Component\HttpFoundation\Exception\RequestExceptionInterface;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

final class UpdateLocale
{
    public function __invoke(Request $request, string $locale): RedirectResponse
    {
        $page = $this->pageIn($locale, $request->input('to')) ?? localized_route('home', locale: $locale);

        $response = redirect()
            ->to($page)
            ->withCookie(cookie(Locales::COOKIE, $locale, minutes: 60 * 24 * 365, httpOnly: true, sameSite: 'lax'));

        event(new LocaleChosen($locale, $request->user()));

        return $response;
    }

    /**
     * Rebuilt from the parse, never the string as sent, so a host in it is
     * dropped and the switch cannot send a reader off the site. The router is
     * asked because a check on how the string starts is walked past by
     * `/\example.com`, which a browser reads as `//example.com`.
     */
    private function pageIn(string $locale, mixed $to): ?string
    {
        if (! is_string($to)) {
            return null;
        }

        try {
            $asked = Request::create($to);
            $route = Route::getRoutes()->match($asked);
        } catch (HttpExceptionInterface|RequestExceptionInterface) {
            return null;
        }

        return str_starts_with((string) $route->getName(), "{$locale}.") ? '/'.ltrim($asked->getRequestUri(), '/') : null;
    }
}
