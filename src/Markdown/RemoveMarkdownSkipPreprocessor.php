<?php

namespace Steddle\Foundry\Markdown;

use Spatie\MarkdownResponse\Preprocessors\Preprocessor;

/**
 * A regex scan for the matching close tag, not a DOMDocument parse: libxml's
 * HTML parser rewrites a `<dialog>`, `<template>` or a table nested inside
 * one, which Flux's modal and date picker print, and a marked element around
 * one loses its own closing tag in that rewrite.
 */
final class RemoveMarkdownSkipPreprocessor implements Preprocessor
{
    /**
     * One attribute character: anything but a quote or `>`, or a whole
     * quoted value. An Alpine `x-data` holds `=>` and `>` in its object
     * literal, which a bare `[^>]*` reads as the tag's own end.
     */
    private const ATTR = '[^"\'>]|"[^"]*"|\'[^\']*\'';

    public function __invoke(string $html): string
    {
        $offset = 0;

        while (preg_match('/<([a-zA-Z][a-zA-Z0-9-]*)\b(?:'.self::ATTR.')*\bdata-markdown-skip\b(?:'.self::ATTR.')*>/', $html, $match, PREG_OFFSET_CAPTURE, $offset)) {
            $tag = $match[1][0];
            $start = $match[0][1];
            $afterOpen = $start + strlen($match[0][0]);

            $end = $this->findMatchingClose($html, $tag, $afterOpen);

            if ($end === null) {
                $offset = $afterOpen;

                continue;
            }

            $html = substr($html, 0, $start).substr($html, $end);
            $offset = $start;
        }

        return $html;
    }

    private function findMatchingClose(string $html, string $tag, int $offset): ?int
    {
        $depth = 1;
        $pattern = '/<(\/?)'.preg_quote($tag, '/').'\b(?:'.self::ATTR.')*?(\/?)>/i';

        while (preg_match($pattern, $html, $match, PREG_OFFSET_CAPTURE, $offset)) {
            $isClosing = $match[1][0] === '/';
            $isSelfClosing = $match[2][0] === '/';
            $tagEnd = $match[0][1] + strlen($match[0][0]);

            if ($isClosing) {
                $depth--;

                if ($depth === 0) {
                    return $tagEnd;
                }
            } elseif (! $isSelfClosing) {
                $depth++;
            }

            $offset = $tagEnd;
        }

        return null;
    }
}
