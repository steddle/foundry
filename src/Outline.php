<?php

namespace Steddle\Foundry;

use Illuminate\Support\Str;

/**
 * The "On this page" list of a rendered document, read from its own `<h2>`s,
 * so a heading added to a document is listed without a second edit. A heading
 * without an id gets one from its text.
 */
final class Outline
{
    /**
     * @return array{html: string, headings: array<string, string>}
     */
    public static function of(string $html): array
    {
        $headings = [];

        $html = preg_replace_callback('#<h2(\s[^>]*)?>(.*?)</h2>#s', function (array $match) use (&$headings): string {
            $attributes = $match[1] ?? '';
            $label = trim(html_entity_decode(strip_tags($match[2]), ENT_QUOTES | ENT_HTML5));

            if (preg_match('#\sid="([^"]+)"#', $attributes, $id)) {
                $headings[$id[1]] = $label;

                return $match[0];
            }

            $id = Str::slug($label);
            $headings[$id] = $label;

            return '<h2 id="'.$id.'"'.$attributes.'>'.$match[2].'</h2>';
        }, $html);

        return ['html' => $html, 'headings' => $headings];
    }

    /**
     * The headings as the links of an "On this page" list.
     *
     * @param  array<string, string>  $headings  id => label
     * @return list<array{label: string, href: string}>
     */
    public static function links(array $headings): array
    {
        return collect($headings)->map(fn (string $label, string $id): array => ['label' => $label, 'href' => '#'.$id])->values()->all();
    }
}
