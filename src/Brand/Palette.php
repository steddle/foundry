<?php

namespace Steddle\Foundry\Brand;

/**
 * A palette's colours as hex, read from foundry.css, where the palettes are
 * written: a mail inlines its colours and resolves no variables.
 */
final class Palette
{
    private static ?string $css = null;

    /** `zinc-900` or `primary-300` in the palette `sendnda`, or null where the palette or the colour is none of the foundry's. */
    public static function colour(string $palette, string $colour): ?string
    {
        if (! preg_match("/:root\[data-palette='".preg_quote($palette, '/')."'\]\s*\{([^}]*)\}/", self::css(), $block)) {
            return null;
        }

        $value = self::declared($block[1], $colour);

        while ($value !== null && preg_match('/^var\(--color-([a-z0-9-]+)\)$/', $value, $reference)) {
            $value = self::declared(self::css(), $reference[1]);
        }

        return $value;
    }

    private static function declared(string $css, string $colour): ?string
    {
        return preg_match('/--color-'.preg_quote($colour, '/').':\s*([^;]+);/', $css, $match) ? trim($match[1]) : null;
    }

    private static function css(): string
    {
        return self::$css ??= (string) file_get_contents(dirname(__DIR__, 2).'/resources/css/foundry.css');
    }
}
