<?php

namespace Steddle\Foundry\Markdown;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\MarkdownConverter;
use League\HTMLToMarkdown\Converter\TableConverter;
use League\HTMLToMarkdown\HtmlConverter;

/**
 * Markdown as the rich editor (`flux:editor`, Tiptap) holds it, as HTML, and back: the editor is
 * loaded with the HTML and saves markdown, with links left as written. A text whose markdown does
 * not come back the same, whitespace aside, is edited as markdown instead.
 */
class RichText
{
    /**
     * Stands in for a hard break while the HTML is converted, so it is written as a backslash at the
     * line's end, and no other line ending is taken for one.
     */
    private const BREAK = "\u{E000}";

    private MarkdownConverter $toHtml;

    private HtmlConverter $toMarkdown;

    public function __construct()
    {
        $environment = new Environment(['html_input' => 'escape', 'allow_unsafe_links' => false]);
        $environment->addExtension(new CommonMarkCoreExtension);
        $environment->addExtension(new GithubFlavoredMarkdownExtension);
        $this->toHtml = new MarkdownConverter($environment);

        $this->toMarkdown = new HtmlConverter(['header_style' => 'atx', 'strip_tags' => true]);
        $this->toMarkdown->getEnvironment()->addConverter(new TableConverter);
    }

    public function html(string $markdown): string
    {
        $html = $this->toHtml->convert($markdown)->getContent();

        // The editor has no task list: a box stays text, `[x]` or `[ ]`, as the markdown writes it.
        $html = preg_replace_callback('#<input (?:checked="" )?disabled="" type="checkbox">#', fn (array $box): string => str_contains($box[0], 'checked') ? '[x]' : '[ ]', $html);

        // Tiptap drops a link to a relative path that opens on a folder name (company/thesis.md) as an
        // unknown scheme, and keeps it written ./company/thesis.md; markdown() takes the ./ off again.
        return preg_replace('#href="(?![a-z][a-z0-9+.\-]*:|\.{0,2}/|\#)([^"/]+/[^"]*)"#i', 'href="./$1"', $html);
    }

    /**
     * The editor's HTML as markdown. Tiptap keeps no column alignment, so a table whose header row
     * the previous text holds takes that table's divider back.
     */
    public function markdown(string $html, string $previous = ''): string
    {
        // Tiptap wraps a table cell's text in a paragraph, which the table converter would break onto lines of its own.
        $html = preg_replace('#<(t[hd])([^>]*)>\s*<p>(.*?)</p>\s*</\1>#s', '<$1$2>$3</$1>', $html);
        $html = preg_replace('#<br\s*/?>\n?#', self::BREAK, $html);

        $markdown = $this->toMarkdown->convert($html);
        $markdown = preg_replace('/'.self::BREAK.'\s*/u', "\\\n", $markdown);
        $markdown = preg_replace('#\]\((<?)\./#', ']($1', $markdown);

        $markdown = preg_replace_callback('/^\|(?:\s*:?-+:?\s*\|)+$/m', fn (array $row): string => strtr($row[0], [':-:' => ':---:', '--:' => '---:', ':--' => ':---']), $markdown);
        $markdown = preg_replace_callback('/\]\(([^)\s]*%20[^)\s]*)\)/', fn (array $link): string => '](<'.rawurldecode($link[1]).'>)', $markdown);
        // GFM links a bare address or URL itself; written back bare, as markdown writes them.
        $markdown = preg_replace('/(?<=\w)\\\\_(?=\w)/', '_', $markdown);
        $markdown = preg_replace('/<(https?:\/\/[^>\s]+|[^<>\s@]+@[^>\s]+)>/', '$1', $markdown);
        $markdown = preg_replace('/\[((?:https?:\/\/)?(?:www\.)?([^\]\s]+))\]\(https?:\/\/(?:www\.)?\2\)/', '$1', $markdown);
        $markdown = preg_replace('/(?<!^)\\\\#/m', '#', $markdown);
        $markdown = $this->alignedAs($markdown, $previous);

        return rtrim(str_replace(['\\[', '\\]', '&amp;', '&lt;', '&gt;', '&quot;', '&#39;'], ['[', ']', '&', '<', '>', '"', "'"], $markdown))."\n";
    }

    public function survives(string $markdown): bool
    {
        // Tiptap's inline code takes no other mark, so bold around a code span comes back split.
        preg_match_all('/\*\*(\S(?:[^\n]*?\S)?)\*\*/', $markdown, $bold);

        if (collect($bold[1])->contains(fn (string $span): bool => str_contains($span, '`'))) {
            return false;
        }

        return self::normalise($this->markdown($this->html($markdown), $markdown)) === self::normalise($markdown);
    }

    private function alignedAs(string $markdown, string $previous): string
    {
        preg_match_all('/^(\|.*\|)\n(\|(?:\s*:?-+:?\s*\|)+)$/m', $previous, $tables, PREG_SET_ORDER);
        $dividers = collect($tables)->mapWithKeys(fn (array $table): array => [self::normalise($table[1]) => $table[2]]);

        return preg_replace_callback('/^(\|.*\|)\n(\|(?:\s*:?-+:?\s*\|)+)$/m', fn (array $table): string => $table[1]."\n".($dividers[self::normalise($table[1])] ?? $table[2]), $markdown);
    }

    public static function normalise(string $markdown): string
    {
        return trim(preg_replace('/\s+/', ' ', $markdown));
    }
}
