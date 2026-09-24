<?php

namespace Steddle\Foundry\Concerns;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * An account that names itself once, on /welcome, before any signed-in page:
 * one made by a magic link, an invitation or a guest send carries the local
 * part of its address as its name until then.
 *
 * @property ?Carbon $onboarded_at
 */
trait HasOnboarding
{
    public function initializeHasOnboarding(): void
    {
        $this->mergeCasts(['onboarded_at' => 'datetime']);
    }

    public function hasOnboarded(): bool
    {
        return $this->onboarded_at !== null;
    }

    /** Whether the name is none, or only the local part of the address. */
    public function hasPlaceholderName(): bool
    {
        $name = Str::lower(trim((string) $this->name));

        return $name === '' || $name === Str::lower(trim(Str::before((string) $this->email, '@')));
    }
}
