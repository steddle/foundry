<?php

namespace Steddle\Foundry\Mail;

use Steddle\Foundry\Brand\BrandAssets;

/**
 * What the foundry's mail theme sets around a message: each option as the
 * mailable gives it, else as `imprint.mail` states it, else the foundry's
 * default. False leaves the footer line or the legal line out; a legal line
 * of the imprint's own stands in for the copyright and the house alike.
 */
final class MailOptions
{
    /**
     * A client, the product a sign-in mail is for, takes the last line as
     * '{name}, by Steddle' where the mailable states none.
     *
     * @param  array<string, string>|null  $links
     * @param  array{name: string}|null  $client
     * @return array{header: string, footer: ?string, links: array<string, string>, legal: ?string, service: bool}
     */
    public static function resolve(?string $header = null, string|false|null $footer = null, ?array $links = null, string|false|null $legal = null, ?array $client = null): array
    {
        $mail = config('imprint.mail', []);

        $footer ??= $mail['footer'] ?? config('imprint.disclaimer');
        $legal ??= $client === null ? $mail['legal'] ?? null : __('foundry::mail.by_steddle', ['name' => $client['name']]);

        return [
            'header' => $header ?? $mail['header'] ?? 'lockup',
            'footer' => $footer ? __($footer) : null,
            'links' => collect($links ?? $mail['links'] ?? [])->mapWithKeys(fn (string $href, string $label): array => [__($label) => $href])->all(),
            'legal' => match (true) {
                $legal === null => '© '.date('Y').' '.config('imprint.name'),
                $legal === false => null,
                default => __($legal),
            },
            'service' => $legal === null && config('imprint.endorsed', true),
        ];
    }

    /**
     * The address of a mail image foundry:assets renders, `mail-logo` or
     * `mail-steddle-logo`, marked with its version so an inbox fetches it
     * anew when it changes; null until the imprint has rendered it.
     */
    public static function image(string $name): ?string
    {
        $asset = BrandAssets::mail()[$name] ?? null;

        if ($asset === null || ! is_file(public_path($asset->path))) {
            return null;
        }

        $version = substr(BrandAssets::manifest()[$name] ?? '', 0, 8);

        return asset($asset->path).($version === '' ? '' : '?v='.$version);
    }
}
