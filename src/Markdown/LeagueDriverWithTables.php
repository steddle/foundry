<?php

namespace Steddle\Foundry\Markdown;

use League\HTMLToMarkdown\Converter\TableConverter;
use League\HTMLToMarkdown\HtmlConverter;
use Spatie\MarkdownResponse\Drivers\LeagueDriver;

/**
 * The package's league driver, with the converter's table support switched
 * on: without it a docs table reaches the markdown as one run-on line.
 */
final class LeagueDriverWithTables extends LeagueDriver
{
    public function convert(string $html): string
    {
        $converter = new HtmlConverter($this->options);
        $converter->getEnvironment()->addConverter(new TableConverter);

        return $converter->convert($html);
    }
}
