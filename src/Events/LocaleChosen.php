<?php

namespace Steddle\Foundry\Events;

use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Fired by the language switch once it has set the `locale` cookie. The user
 * is null for a guest.
 */
final class LocaleChosen
{
    public function __construct(
        public readonly string $locale,
        public readonly ?Authenticatable $user,
    ) {}
}
