<?php

namespace Steddle\Foundry\Mcp;

use Laravel\Mcp\Enums\IconTheme;
use Laravel\Mcp\Schema\Icon;

/**
 * On an imprint's MCP server: the two icons `foundry:assets` draws where
 * `imprint.mcp_icons` names them, the dark one for a light client and the
 * light one for a dark client, and the favicon at any size.
 */
trait HasImprintIcons
{
    /**
     * @return list<Icon>
     */
    protected function icons(): array
    {
        if (! $name = config('imprint.mcp_icons')) {
            return [];
        }

        return [
            Icon::from("/{$name}-128.png", 'image/png', ['128x128'], IconTheme::Light),
            Icon::from("/{$name}-light-128.png", 'image/png', ['128x128'], IconTheme::Dark),
            Icon::from('/favicon.svg', 'image/svg+xml', ['any']),
        ];
    }
}
