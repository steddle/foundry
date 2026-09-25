<?php

namespace Steddle\Foundry\Auth;

use BadMethodCallException;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\User;
use Steddle\Foundry\Account;

/**
 * The Steddle account's server hands the account and the credential it signed
 * in with over in the token response, so no request asks for it by token.
 * `$user->credential` is `['kind' => 'passkey'|'magic_link'|'session', 'id' => ?string]`.
 */
class SteddleProvider extends AbstractProvider
{
    protected $usesPKCE = true;

    public function user(): User
    {
        if ($this->user) {
            return $this->user;
        }

        if ($this->hasInvalidState()) {
            throw new InvalidStateException;
        }

        $response = $this->getAccessTokenResponse($this->getCode());

        return $this->userInstance($response, $response['user'])->map(['credential' => $response['credential']]);
    }

    /**
     * Through Laravel's client, where Socialite's own is a bare Guzzle: an
     * imprint's suite fakes it with `Http::fake()`.
     *
     * @return array<string, mixed>
     */
    public function getAccessTokenResponse($code): array
    {
        return Http::asForm()->acceptJson()
            ->post($this->getTokenUrl(), $this->getTokenFields($code))
            ->throw()
            ->json();
    }

    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase(Account::server('oauth/authorize'), $state);
    }

    protected function getTokenUrl(): string
    {
        return Account::server('oauth/token');
    }

    protected function getUserByToken($token): array
    {
        throw new BadMethodCallException('The Steddle account arrives with the token.');
    }

    /** @param  array{id: string, email: string, name: string, locale: ?string}  $user */
    protected function mapUserToObject(array $user): User
    {
        return (new User)->setRaw($user)->map([
            'id' => $user['id'],
            'email' => $user['email'],
            'name' => $user['name'],
        ]);
    }
}
