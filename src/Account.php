<?php

namespace Steddle\Foundry;

final class Account
{
    public const SERVER = 'https://auth.steddle.com';

    public static function server(string $path = ''): string
    {
        return rtrim((string) (config('imprint.account.server') ?? self::SERVER), '/').($path === '' ? '' : '/'.ltrim($path, '/'));
    }
}
