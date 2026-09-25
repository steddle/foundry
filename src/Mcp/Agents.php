<?php

namespace Steddle\Foundry\Mcp;

use Illuminate\Support\Str;

final class Agents
{
    public static function url(): string
    {
        return url('mcp');
    }

    public static function command(): string
    {
        return 'claude mcp add --transport http '.Str::slug(config('imprint.name')).' '.self::url();
    }

    /**
     * A client's name without the server name Claude Code registers it under,
     * 'Claude Code (bron)', which is the name the reader gave the imprint.
     */
    public static function name(string $name): string
    {
        return preg_replace('/\s*\([^)]*\)$/', '', $name);
    }

    /**
     * Where a client receives its access. Anyone can register a client under
     * any name; its redirect hosts are what tell one apart.
     *
     * @param  list<string>  $redirectUris
     */
    public static function hosts(array $redirectUris): string
    {
        return collect($redirectUris)->map(fn (string $uri): string => parse_url($uri, PHP_URL_HOST) ?: $uri)->unique()->implode(', ');
    }
}
