<?php

namespace Steddle\Foundry\Concerns;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Watson\Nameable\Name;

/**
 * `$user->initials` for a model with a `name`, as the account menu and the
 * account row show them: two letters at most, upper case whatever the name's
 * case, and without
 * words in brackets, which Nameable drops, so 'ada visser (Northwind)' reads AV.
 */
trait HasInitials
{
    protected function initials(): Attribute
    {
        return Attribute::get(fn (): string => mb_strtoupper(mb_substr(Name::from($this->name)->initials(), 0, 2)));
    }
}
