<?php

namespace Steddle\Foundry\Concerns;

use Illuminate\Support\Facades\Auth;
use Laravel\Passkeys\Actions\DeletePasskey;
use Livewire\Attributes\Locked;

/**
 * For foundry:passkeys. Needs laravel/passkeys.
 */
trait ManagesPasskeys
{
    /**
     * @var list<array{id: int, name: string, authenticator: string|null, created_at: string, last_used_at: string|null}>
     */
    #[Locked]
    public array $passkeys = [];

    public function mountManagesPasskeys(): void
    {
        $this->loadPasskeys();
    }

    public function loadPasskeys(): void
    {
        $this->passkeys = Auth::user()->passkeys()
            ->latest()
            ->get()
            ->map(fn ($passkey): array => [
                'id' => $passkey->id,
                'name' => $passkey->name,
                'authenticator' => $passkey->authenticator,
                'created_at' => $passkey->created_at->diffForHumans(),
                'last_used_at' => $passkey->last_used_at?->diffForHumans(),
            ])
            ->all();
    }

    public function deletePasskey(int $id, DeletePasskey $deletePasskey): void
    {
        $user = Auth::user();

        $deletePasskey($user, $user->passkeys()->findOrFail($id));

        $this->loadPasskeys();
    }
}
