<?php

namespace Steddle\Foundry\Annotations;

/**
 * The text of an HTML fragment as the browser's `textContent` reads it once the fragment is on the
 * page: tags and comments gone, entities decoded, whitespace kept. Offsets into one are offsets
 * into the other, so a passage the server finds is the passage foundry:annotated paints.
 */
final class PlainText
{
    public static function fromHtml(string $html): string
    {
        $html = str_replace("\r\n", "\n", $html);

        // The parser drops a newline that opens a <pre> or a <textarea>.
        $html = preg_replace('#(<(?:pre|textarea)\b[^>]*>)\n#i', '$1', $html);

        return html_entity_decode(strip_tags(preg_replace('/<!--.*?-->/s', '', $html)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
