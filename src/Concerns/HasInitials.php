<?php

namespace Steddle\Foundry\Concerns;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Watson\Nameable\Name;

/**
 * Two letters at most, upper case, without words in brackets, which Nameable
 * drops: 'ada visser (Northwind)' reads AV.
 */
trait HasInitials
{
    protected function initials(): Attribute
    {
        return Attribute::get(fn (): string => mb_strtoupper(mb_substr(Name::from($this->name)->initials(), 0, 2)));
    }
}
