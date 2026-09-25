<?php

namespace Steddle\Foundry\Auth;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/**
 * `purpose` and `intended` are the imprint's to set, and
 * `imprint.auth.redirect` reads them after the sign-in; columns of the
 * imprint's own go in through `issue()`'s `$attributes`.
 *
 * @property int $user_id
 * @property string $token_hash
 * @property string $purpose
 * @property ?string $intended
 * @property CarbonImmutable $expires_at
 * @property ?CarbonImmutable $used_at
 * @property ?string $used_ip
 */
class MagicLink extends Model
{
    use Prunable;

    public const LOGIN = 'login';

    public const MINUTES = 15;

    protected $guarded = [];

    /** The link as mailed, on the instance `issue()` returns: the table holds only its token's hash. */
    public ?string $url = null;

    protected function casts(): array
    {
        return [
            'expires_at' => 'immutable_datetime',
            'used_at' => 'immutable_datetime',
        ];
    }

    /**
     * The URL is signed without an expiry: the row decides whether the link
     * still works, so an expired link reaches a page that says so, not a 403.
     *
     * @param  array<string, mixed>  $attributes
     */
    public static function issue(Model $user, string $purpose = self::LOGIN, ?string $intended = null, ?DateTimeInterface $expiresAt = null, array $attributes = []): self
    {
        $token = Str::random(40);

        $link = static::create([
            ...$attributes,
            'user_id' => $user->getKey(),
            'token_hash' => hash('sha256', $token),
            'purpose' => $purpose,
            'intended' => $intended,
            'expires_at' => $expiresAt ?? now()->addMinutes(self::MINUTES),
        ]);

        // Signed relative and rooted on app.url: a forged Host header can neither redirect the mailed token nor pass the signature.
        $link->url = rtrim((string) config('app.url'), '/').URL::signedRoute('login.magic', ['user' => $user->getKey(), 'token' => $token], absolute: false);

        return $link;
    }

    public static function findByToken(string $token): ?self
    {
        return static::firstWhere('token_hash', hash('sha256', $token));
    }

    /**
     * Marks the link used under a lock, so a forwarded link or a double
     * submit signs no one in a second time. Null where it is not the user's
     * or no longer usable.
     */
    public static function consume(string $token, int|string $user, ?string $ip): ?self
    {
        return DB::transaction(function () use ($token, $user, $ip): ?self {
            $link = static::query()->where('token_hash', hash('sha256', $token))->lockForUpdate()->first();

            if ($link === null || (string) $link->user_id !== (string) $user || ! $link->isUsable()) {
                return null;
            }

            $link->update(['used_at' => now(), 'used_ip' => $ip]);

            return $link;
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    public function isUsable(): bool
    {
        return $this->used_at === null && $this->expires_at->isFuture();
    }

    /** A row outlives its link by `imprint.auth.keep` days, a day by default: long enough for an expired link's page to read what it was for. */
    public function prunable(): Builder
    {
        return static::where('expires_at', '<', now()->subDays((int) config('imprint.auth.keep', 1)));
    }
}
