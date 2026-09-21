<?php

namespace Steddle\Foundry;

use Illuminate\Support\Facades\View;

/**
 * The imprint's docs and legal documents: a table of contents in
 * `resources/content/{locale}/{name}.php`, or `resources/content/{name}.php`
 * for an imprint in one language, and a view per entry. An entry is published
 * where its view exists and nowhere else, so a page listed before it is
 * written is no link to a 404.
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
     * The docs' topics, each with the articles that have a view.
     *
     * @return array<string, array{title: string, icon: string, description: string, articles: array<string, array<int|string, mixed>>}>
     */
    public static function docs(?string $locale = null): array
    {
        return collect(self::read('docs', $locale))
            ->map(fn (array $topic): array => [...$topic, 'articles' => array_filter($topic['articles'], fn (string $slug): bool => self::view('docs.articles', $slug, $locale) !== null, ARRAY_FILTER_USE_KEY)])
            ->filter(fn (array $topic): bool => $topic['articles'] !== [])
            ->all();
    }

    /**
     * The legal documents' audiences, each with the documents that have a view.
     *
     * @return array{promises: list<array{0: string, 1: string}>, audiences: array<string, array<string, mixed>>}
     */
    public static function legal(?string $locale = null): array
    {
        $legal = self::read('legal', $locale);
        $legal['audiences'] = collect($legal['audiences'])
            ->map(fn (array $audience): array => [...$audience, 'documents' => array_filter($audience['documents'], fn (string $slug): bool => self::view('legal.documents', $slug, $locale) !== null, ARRAY_FILTER_USE_KEY)])
            ->filter(fn (array $audience): bool => $audience['documents'] !== [])
            ->all();

        return $legal;
    }

    /** A value from `imprint.{section}`, a translation key or the words themselves. */
    public static function copy(string $section, string $key): ?string
    {
        $value = config("imprint.{$section}.{$key}");

        return $value === null ? null : __($value);
    }
}
