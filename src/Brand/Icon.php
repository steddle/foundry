<?php

namespace Steddle\Foundry\Brand;

use Illuminate\Support\Facades\Blade;
use InvalidArgumentException;

/**
 * The imprint's mark on a tile, as an SVG. `tile` is ink with the mark in
 * paper and rounded corners, `light` the same inverted, `adaptive` the tile
 * that inverts in dark mode, and `full` a square to the edge with the mark at
 * half size, for a platform that crops or rounds the icon itself.
 */
final class Icon
{
    public static function svg(string $variant, ?int $size = null): string
    {
        [$ink, $paper] = [config('imprint.ink'), config('imprint.paper')];

        if (blank($ink) || blank($paper)) {
            throw new InvalidArgumentException('config/imprint.php states no ink or paper colour.');
        }

        $mark = self::mark();
        $name = e(config('imprint.name'));
        $dimensions = $size ? " width=\"{$size}\" height=\"{$size}\"" : '';
        $open = "<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 32 32\"{$dimensions} role=\"img\" aria-label=\"{$name}\">";
        $tile = 'transform="translate(4.16 4.16) scale(0.74)"';

        return match ($variant) {
            'tile' => "{$open}<rect width=\"32\" height=\"32\" rx=\"5\" fill=\"{$ink}\"/><g fill=\"{$paper}\" {$tile}>{$mark}</g></svg>",
            'light' => "{$open}<rect width=\"32\" height=\"32\" rx=\"5\" fill=\"{$paper}\"/><g fill=\"{$ink}\" {$tile}>{$mark}</g></svg>",
            'adaptive' => "{$open}<style>.t{fill:{$ink}}.m{fill:{$paper}}@media (prefers-color-scheme:dark){.t{fill:{$paper}}.m{fill:{$ink}}}</style><rect class=\"t\" width=\"32\" height=\"32\" rx=\"5\"/><g class=\"m\" {$tile}>{$mark}</g></svg>",
            'full' => "{$open}<rect width=\"32\" height=\"32\" fill=\"{$ink}\"/><g fill=\"{$paper}\" transform=\"translate(8 8) scale(0.5)\">{$mark}</g></svg>",
            default => throw new InvalidArgumentException("No icon variant {$variant}."),
        };
    }

    /**
     * The shapes of the imprint's own x-site.mark, drawn on a 32 by 32 grid,
     * without the colour the mark takes from its ground.
     */
    private static function mark(): string
    {
        $svg = Blade::render('<x-site.mark />');

        if (! preg_match('/<svg[^>]*viewBox="0 0 32 32"[^>]*>(.*)<\/svg>/s', $svg, $match)) {
            throw new InvalidArgumentException('x-site.mark is no SVG on a 32 by 32 grid.');
        }

        return trim(str_replace(' fill="currentColor"', '', $match[1]));
    }
}
