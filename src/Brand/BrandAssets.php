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

        return collect([
            new BrandAsset('og-image', 'og-image.png', 1200, 630, 'og:image where no OG Kit key is set', 'site.og-image', $og),
            new BrandAsset('social-preview', 'brand/social/github-social-preview.png', 1280, 640, 'GitHub repository settings, social preview', 'site.social-preview', $og),
            new BrandAsset('readme-banner-light', 'brand/social/readme-banner-light.png', 1600, 520, 'README <picture>, light source', 'site.readme-banner', $banner),
            new BrandAsset('readme-banner-dark', 'brand/social/readme-banner-dark.png', 1600, 520, 'README <picture>, dark source', 'site.readme-banner', [...$banner, 'dark' => true]),
        ])->keyBy('name')->all();
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
