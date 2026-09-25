<?php

namespace Steddle\Foundry\Concerns;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * @property ?string $steddle_id
 */
trait HasSteddleAccount
{
    /**
     * By its Steddle id, else a row with its address that no account has
     * linked yet, which it links, else a new row.
     *
     * @param  array{id: string, email: string, name: string, locale?: ?string}  $identity
     */
    public static function fromSteddleAccount(array $identity): static
    {
        $user = static::query()->where('steddle_id', $identity['id'])->first()
            ?? static::query()->whereNull('steddle_id')->whereRaw('lower(email) = ?', [Str::lower($identity['email'])])->first()
            ?? new static;

        $user->forceFill(['steddle_id' => $identity['id'], 'email_verified_at' => $user->email_verified_at ?? now()])
            ->fillFromSteddleAccount($identity)
            ->save();

        return $user;
    }

    /** @param  array{email?: string, name?: string, locale?: ?string}  $identity */
    public function fillFromSteddleAccount(array $identity): static
    {
        // A row no account has linked may hold the new address, and the unique index would refuse it: the row keeps its old one.
        $taken = $this->exists && isset($identity['email'])
            && static::query()->whereKeyNot($this->getKey())->whereRaw('lower(email) = ?', [Str::lower($identity['email'])])->exists();

        $columns = $taken ? ['name'] : ['email', 'name'];

        if ($this->getConnection()->getSchemaBuilder()->hasColumn($this->getTable(), 'locale')) {
            $columns[] = 'locale';
        }

        return $this->forceFill(Arr::only($identity, $columns));
    }

    /**
     * The remember cookie recalls no one whose password is not a string, and
     * an account here has none. An empty hash matches no password.
     */
    public function getAuthPassword(): string
    {
        return (string) $this->getAttribute($this->getAuthPasswordName());
    }

    /** Its sessions on the database driver, and a new remember token, which outlasts them. */
    public function signOutEverywhere(): void
    {
        if (config('session.driver') === 'database') {
            DB::connection(config('session.connection'))->table(config('session.table', 'sessions'))->where('user_id', $this->getAuthIdentifier())->delete();
        }

        $this->setRememberToken(Str::random(60));
        $this->save();
    }
}
