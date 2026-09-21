<?php

namespace Steddle\Foundry;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Arr;

/**
 * The languages `imprint.locales` names, the first of them at the root and
 * every other under its own prefix. One language is a site that is not
 * multilingual: no prefix, no switch, no alternates. Translated paths differ,
 * so a page finds its counterpart by route name, `{locale}.{page}`, and
 * `lang/{locale}/routes.php` holds each language's path words.
 */
final class Locales
{
    /** Plain, so whatever caches in front can key on it. */
    public const COOKIE = 'locale';

    /** @return list<string> */
    public static function all(): array
    {
        return array_keys(config('imprint.locales') ?: [config('app.locale') => []]);
    }

    /** The language whose pages carry no prefix. */
    public static function root(): string
    {
        return self::all()[0];
    }

    public static function multilingual(): bool
    {
        return count(self::all()) > 1;
    }

    /** The Open Graph locale, `nl_NL`, where the imprint states one. */
    public static function regional(string $locale): string
    {
        return config("imprint.locales.{$locale}.regional") ?? $locale;
    }

    public static function preferred(Request $request): string
    {
        $chosen = $request->cookie(self::COOKIE);

        if (is_string($chosen) && in_array($chosen, self::all(), true)) {
            return $chosen;
        }

        return $request->getPreferredLanguage([self::root(), ...array_diff(self::all(), [self::root()])]) ?? self::root();
    }

    /**
     * The language a failure is shown in: the one its route states, else a
     * prefix in the address, else, where nothing was routed, what the visitor
     * prefers. Null for a route that states none.
     */
    public static function ofFailure(Request $request): ?string
    {
        $route = $request->route();
        $stated = $route instanceof Route ? self::stated($route) : null;

        if ($stated !== null) {
            return $stated;
        }

        $first = (string) $request->segment(1);

        if ($first !== self::root() && in_array($first, self::all(), true)) {
            return $first;
        }

        return $route instanceof Route ? null : self::preferred($request);
    }

    /**
     * The request's page in another language, with its query, or null where
     * the request is for none of the site's pages or the page is not published
     * in that language.
     */
    public static function counterpart(Request $request, string $locale, bool $absolute = true): ?string
    {
        $route = $request->route();

        if (! $route instanceof Route) {
            return null;
        }

        [$language, $page] = array_pad(explode('.', (string) $route->getName(), 2), 2, null);

        if ($page === null || ! in_array($language, self::all(), true)) {
            return null;
        }

        // A page published in one language has no counterpart in the other.
        if (! app('router')->has("{$locale}.{$page}")) {
            return null;
        }

        // `Route::view` adds view and status parameters that do not belong in the URL, and a query never overrides the route's own.
        $parameters = Arr::only($route->parameters(), $route->parameterNames());

        return route("{$locale}.{$page}", [...$request->query(), ...$parameters], $absolute);
    }

    /** The locale `Route::localized()` gave the route's group, which a route carries in its action. */
    private static function stated(Route $route): ?string
    {
        $locale = $route->getAction('locale');

        return is_string($locale) ? $locale : null;
    }
}
