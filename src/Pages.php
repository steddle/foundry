<?php

namespace Steddle\Foundry;

use Closure;
use Illuminate\Support\Facades\App;

/**
 * The imprint's public pages, from the class `imprint.sitemap` names: its
 * static `all()` answers, in the current locale and in the order the sitemap
 * and llms.txt list them, `key => [title, description, url, render]`, where
 * `render` builds the page's HTML for llms-full.txt, or is null for a page a
 * controller answers. Its optional static `sections()` answers what llms.txt
 * says beyond the pages, `heading => lines`.
 */
final class Pages
{
    /**
     * The pages in every locale, `locale => key => page`.
     *
     * @return array<string, array<string, array{title: string, description: string, url: string, render: (Closure(): string)|null}>>
     */
    public static function byLocale(): array
    {
        return collect(Locales::all())->mapWithKeys(fn (string $locale): array => [$locale => self::in($locale, fn (): array => config('imprint.sitemap')::all())])->all();
    }

    /** @return array<string, list<string>> */
    public static function sections(): array
    {
        $class = config('imprint.sitemap');

        return method_exists($class, 'sections') ? $class::sections() : [];
    }

    /**
     * @template T
     *
     * @param  Closure(): T  $callback
     * @return T
     */
    public static function in(string $locale, Closure $callback): mixed
    {
        $previous = App::getLocale();
        App::setLocale($locale);

        try {
            return $callback();
        } finally {
            App::setLocale($previous);
        }
    }
}
