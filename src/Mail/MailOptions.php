<?php

namespace Steddle\Foundry\Mail;

/**
 * What the foundry's mail theme sets around a message: each option as the
 * mailable gives it, else as `imprint.mail` states it, else the foundry's
 * default. False leaves the footer line or the legal line out.
 */
final class MailOptions
{
    /**
     * @param  array<string, string>|null  $links
     * @return array{header: string, footer: ?string, links: array<string, string>, legal: ?string}
     */
    public static function resolve(?string $header = null, string|false|null $footer = null, ?array $links = null, string|false|null $legal = null): array
    {
        $mail = config('imprint.mail', []);

        $footer ??= $mail['footer'] ?? config('imprint.disclaimer');
        $legal ??= $mail['legal'] ?? '© '.date('Y').' '.config('imprint.name').(config('imprint.endorsed', true) ? ' | '.__('foundry::footer.service_by').' Steddle' : '');

        return [
            'header' => $header ?? $mail['header'] ?? 'lockup',
            'footer' => $footer ? __($footer) : null,
            'links' => collect($links ?? $mail['links'] ?? [])->mapWithKeys(fn (string $href, string $label): array => [__($label) => $href])->all(),
            'legal' => $legal ? __($legal) : null,
        ];
    }
}
