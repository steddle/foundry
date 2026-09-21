<?php

use Steddle\Foundry\Locales;

if (! function_exists('localized_route')) {
    /**
     * A page's route in the current locale, or in the one given. An imprint in
     * one language names its pages without a locale.
     *
     * @param  mixed  $parameters  a route's parameters, as `route()` takes them
     */
    function localized_route(string $name, mixed $parameters = [], ?string $locale = null): string
    {
        return route(Locales::multilingual() ? ($locale ?? app()->getLocale()).'.'.$name : $name, $parameters);
    }
}

if (! function_exists('localized_path')) {
    /**
     * A path spelled in English, in the words of a locale and under its
     * prefix. A placeholder, an identifier and a word the locale has no word
     * of its own for stand as written.
     */
    function localized_path(string $path, string $locale): string
    {
        /** @var array<string, array<string, string>> $words */
        static $words = [];

        $words[$locale] ??= is_file(lang_path("{$locale}/routes.php")) ? require lang_path("{$locale}/routes.php") : [];

        $segments = array_map(
            fn (string $segment): string => $words[$locale][$segment] ?? $segment,
            array_filter(explode('/', $path), fn (string $segment): bool => $segment !== ''),
        );

        return implode('/', [...($locale === Locales::root() ? [] : [$locale]), ...$segments]);
    }
}

if (! function_exists('localized_alternates')) {
    /**
     * This page in every locale, with its query, or that locale's home page
     * where the request is for none of the site's pages.
     *
     * @return array<string, string>
     */
    function localized_alternates(bool $absolute = true): array
    {
        $alternates = [];

        foreach (Locales::all() as $locale) {
            $alternates[$locale] = Locales::counterpart(request(), $locale, $absolute)
                ?? route("{$locale}.home", absolute: $absolute);
        }

        return $alternates;
    }
}

if (! function_exists('localized_url')) {
    /**
     * A path spelled in English, as an address in the current locale or in
     * the one given. For a page that has no route to name yet.
     */
    function localized_url(string $path = '', ?string $locale = null): string
    {
        return url(localized_path($path, $locale ?? app()->getLocale()));
    }
}
