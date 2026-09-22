<?php

namespace Steddle\Foundry\Brand;

use Illuminate\Support\Facades\File;
use InvalidArgumentException;

/**
 * The images every imprint publishes, filled from its `config/imprint.php`.
 */
final class BrandAssets
{
    public const MANIFEST = 'brand/assets.json';

    /**
     * @return array<string, BrandAsset>
     */
    public static function all(): array
    {
        $og = self::copy('og');
        $banner = self::copy('banner');

        $assets = [
            new BrandAsset('og-image', 'og-image.png', 1200, 630, 'og:image where no OG Kit key is set', 'foundry::og-image', $og),
            new BrandAsset('social-preview', 'brand/social/github-social-preview.png', 1280, 640, 'GitHub repository settings, social preview', 'foundry::social-preview', $og),
            new BrandAsset('readme-banner-light', 'brand/social/readme-banner-light.png', 1600, 520, 'README <picture>, light source', 'foundry::readme-banner', $banner),
            new BrandAsset('readme-banner-dark', 'brand/social/readme-banner-dark.png', 1600, 520, 'README <picture>, dark source', 'foundry::readme-banner', [...$banner, 'dark' => true]),
            self::icon('favicon-96', 'favicon-96x96.png', 96, 'foundry:favicons', 'tile'),
            self::icon('apple-touch-icon', 'apple-touch-icon.png', 180, 'foundry:favicons: iOS rounds it itself', 'full'),
            self::icon('manifest-192', 'web-app-manifest-192x192.png', 192, 'site.webmanifest', 'tile'),
            self::icon('icon-512', 'icon-512.png', 512, 'site.webmanifest, and favicon.ico at 16, 32 and 48', 'tile'),
            self::icon('icon-maskable-512', 'icon-maskable-512.png', 512, 'site.webmanifest, maskable', 'full'),
        ];

        if ($mcp = config('imprint.mcp_icons')) {
            $assets[] = self::icon('mcp', "{$mcp}-128.png", 128, 'MCP server icon, light theme', 'tile');
            $assets[] = self::icon('mcp-light', "{$mcp}-light-128.png", 128, 'MCP server icon, dark theme', 'light');
        }

        return collect($assets)->keyBy('name')->all();
    }

    /**
     * The files written as text rather than rendered: the SVG icons and the
     * web app manifest.
     *
     * @return array<string, string>
     */
    public static function files(): array
    {
        $name = config('imprint.name');
        $ink = config('imprint.ink');

        $manifest = implode("\n", [
            '{',
            '    "name": '.json_encode($name).',',
            '    "short_name": '.json_encode($name).',',
            '    "icons": [',
            '        { "src": "/web-app-manifest-192x192.png", "sizes": "192x192", "type": "image/png", "purpose": "any" },',
            '        { "src": "/icon-512.png", "sizes": "512x512", "type": "image/png", "purpose": "any" },',
            '        { "src": "/icon-maskable-512.png", "sizes": "512x512", "type": "image/png", "purpose": "maskable" }',
            '    ],',
            "    \"theme_color\": \"{$ink}\",",
            "    \"background_color\": \"{$ink}\",",
            '    "display": "standalone",',
            '    "start_url": "/"',
            '}',
        ])."\n";

        return [
            'favicon.svg' => Icon::svg('tile')."\n",
            'favicon-adaptive.svg' => Icon::svg('adaptive')."\n",
            'brand/icons/favicon-light.svg' => Icon::svg('light')."\n",
            'brand/icons/maskable.svg' => Icon::svg('full')."\n",
            'site.webmanifest' => $manifest,
        ];
    }

    public static function find(string $name): ?BrandAsset
    {
        return self::all()[$name] ?? null;
    }

    /**
     * @return array<string, string>
     */
    public static function manifest(): array
    {
        $path = public_path(self::MANIFEST);

        return is_file($path) ? File::json($path) : [];
    }

    private static function icon(string $name, string $path, int $size, string $use, string $variant): BrandAsset
    {
        return new BrandAsset($name, $path, $size, $size, $use, 'foundry::brand.icon', ['variant' => $variant, 'size' => $size], 'icon', transparent: $variant !== 'full');
    }

    /**
     * @return array{heading: string, marked?: string, lede?: string, eyebrow?: string}
     */
    private static function copy(string $key): array
    {
        $copy = config("imprint.{$key}");

        if (! is_array($copy) || blank($copy['heading'] ?? null)) {
            throw new InvalidArgumentException("config/imprint.php states no {$key}.heading.");
        }

        $locale = $copy['locale'] ?? null;
        unset($copy['locale']);

        // A value may be a translation key, so a site whose copy lives in lang/
        // states it once; a plain sentence has no translation and stays itself.
        return array_map(
            fn (mixed $value): mixed => is_string($value) ? __($value, [], $locale) : $value,
            array_filter($copy, fn (mixed $value): bool => $value !== null),
        );
    }
}
