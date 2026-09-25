<?php

namespace Steddle\Foundry;

use Illuminate\Support\Facades\View;

/**
 * A table of contents in `resources/content/{locale}/{name}.php`, or
 * `resources/content/{name}.php` in one language, and a view per entry. An
 * entry without its view is left out, so a listed page is never a 404.
 */
final class Content
{
    /** @return array<string, mixed> */
    public static function read(string $name, ?string $locale = null): array
    {
        $locale ??= app()->getLocale();
        $localized = resource_path("content/{$locale}/{$name}.php");

        return require is_file($localized) ? $localized : resource_path("content/{$name}.php");
    }

    /** The view an entry is written in, `{base}.{locale}.{slug}` or `{base}.{slug}`, or null where it has none. */
    public static function view(string $base, string $slug, ?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();

        return collect(["{$base}.{$locale}.{$slug}", "{$base}.{$slug}"])->first(fn (string $view): bool => View::exists($view));
    }

    /**
     * @return array<string, array{title: string, icon: string, description: string, articles: array<string, array<int|string, mixed>>}>
     */
    public static function docs(?string $locale = null): array
    {
        return self::docsIn($locale ?? app()->getLocale());
    }

    /**
     * The locale is an argument so `once` keys on it.
     *
     * @return array<string, array{title: string, icon: string, description: string, articles: array<string, array<int|string, mixed>>}>
     */
    private static function docsIn(string $locale): array
    {
        return once(fn (): array => collect(self::read('docs', $locale))
            ->map(fn (array $topic): array => [...$topic, 'articles' => array_filter($topic['articles'], fn (string $slug): bool => self::view('docs.articles', $slug, $locale) !== null, ARRAY_FILTER_USE_KEY)])
            ->filter(fn (array $topic): bool => $topic['articles'] !== [])
            ->all());
    }

    /**
     * @return array{promises: list<array{0: string, 1: string}>, audiences: array<string, array<string, mixed>>}
     */
    public static function legal(?string $locale = null): array
    {
        return self::legalIn($locale ?? app()->getLocale());
    }

    /**
     * @return array{promises: list<array{0: string, 1: string}>, audiences: array<string, array<string, mixed>>}
     */
    private static function legalIn(string $locale): array
    {
        return once(function () use ($locale): array {
            $legal = self::read('legal', $locale);
            $legal['audiences'] = collect($legal['audiences'])
                ->map(fn (array $audience): array => [...$audience, 'documents' => array_filter($audience['documents'], fn (string $slug): bool => self::view('legal.documents', $slug, $locale) !== null, ARRAY_FILTER_USE_KEY)])
                ->filter(fn (array $audience): bool => $audience['documents'] !== [])
                ->all();

            return $legal;
        });
    }

    /** A value from `imprint.{section}`, a translation key or the words themselves. */
    public static function copy(string $section, string $key): ?string
    {
        $value = config("imprint.{$section}.{$key}");

        return $value === null ? null : __($value);
    }
}
