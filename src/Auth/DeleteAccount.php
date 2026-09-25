<?php

namespace Steddle\Foundry\Auth;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Laravel\Passkeys\Contracts\PasskeyUser;
use Laravel\Passport\Passport;
use Laravel\Sanctum\Sanctum;

/**
 * What the account made stays, for the imprint to keep or delete itself.
 */
final class DeleteAccount
{
    public function __invoke(Model $user): void
    {
        Auth::guard()->logout();

        if (request()->hasSession()) {
            request()->session()->invalidate();
            request()->session()->regenerateToken();
        }

        if (class_exists(Passport::class)) {
            // Passport's tables carry no foreign keys, and `$user->tokens()` skips a token whose client is gone.
            $tokens = Passport::token()->newQuery()->where('user_id', $user->getKey());
            Passport::refreshToken()->newQuery()->whereIn('access_token_id', $tokens->clone()->select('id'))->delete();
            $tokens->delete();
        }

        if (class_exists(Sanctum::class)) {
            Sanctum::personalAccessTokenModel()::query()->whereMorphedTo('tokenable', $user)->delete();
        }

        if ($user instanceof PasskeyUser) {
            $user->passkeys()->delete();
        }

        $user->delete();
    }
}
