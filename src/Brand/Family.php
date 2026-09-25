<?php

namespace Steddle\Foundry\Brand;

/**
 * `ink` is an imprint's zinc-900, `accent` its primary-300, `mark` its
 * pictogram on a 32 by 32 grid.
 */
final class Family
{
    /**
     * @return list<array{name: string, host: string, palette: string, ink: string, accent: string, mark: string}>
     */
    public static function all(): array
    {
        return [
            ['name' => 'Steddle', 'host' => 'steddle.com', 'palette' => 'Stone and lichen', 'ink' => '#0b231c', 'accent' => '#f2e96b', 'mark' => 'M1 12.6C1 8.1 7.4 5 16 5s15 3.1 15 7.6Z M6.8 14.5h18.4L28 27H4Z'],
            ['name' => 'Bron', 'host' => 'bron.steddle.com', 'palette' => 'Well and spring', 'ink' => '#0a212c', 'accent' => '#70d8fe', 'mark' => 'M3.4 5h4.8l1.2 22H1.6Z M23.8 5h4.8l1.8 22h-7.8Z M11.3 18.4h9.4V27h-9.4Z M11.1 12.6h9.8v3.9h-9.8Z M10.9 7.9h10.2v2.8H10.9Z'],
            ['name' => 'Send NDA', 'host' => 'sendnda.com', 'palette' => 'Iron gall and sealing wax', 'ink' => '#2b1720', 'accent' => '#f862b3', 'mark' => 'M5 5H27V9.5L16.9 22.2V13.7A2.1 2.1 0 1 0 15.1 13.7V22.2L5 9.5Z M1.6 24.6h28.8V27H1.6Z'],
        ];
    }
}
