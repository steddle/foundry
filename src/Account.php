<?php

namespace Steddle\Foundry;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;

final class Account
{
    public const SERVER = 'https://auth.steddle.com';

    public static function server(string $path = ''): string
    {
        return rtrim((string) (config('imprint.account.server') ?? self::SERVER), '/').($path === '' ? '' : '/'.ltrim($path, '/'));
    }

    /**
     * The local row for an address, whose Steddle account is made where it
     * has none, unnamed until it signs in: a recipient, or a send without
     * signing in.
     */
    public static function identify(string $email, string $name): Model
    {
        $identity = Http::withBasicAuth((string) config('imprint.account.client'), (string) config('imprint.account.secret'))
            ->acceptJson()
            ->post(self::server('api/identities'), ['email' => $email, 'name' => $name])
            ->throw()
            ->json();

        return config('auth.providers.users.model')::fromSteddleAccount($identity);
    }
}
