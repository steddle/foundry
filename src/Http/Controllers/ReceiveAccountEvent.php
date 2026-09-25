<?php

namespace Steddle\Foundry\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Steddle\Foundry\Auth\DeleteAccount;

/**
 * What the Steddle account's server tells an imprint of an account it holds.
 * It retries anything but a 2xx for a day, so an id this imprint has no row
 * for answers 200.
 */
final class ReceiveAccountEvent
{
    public function __invoke(Request $request): Response
    {
        $secret = (string) config('imprint.account.secret');
        $timestamp = (string) $request->header('Steddle-Timestamp');
        $expected = 'sha256='.hash_hmac('sha256', $timestamp.'.'.$request->getContent(), $secret);

        // An empty secret would sign for anyone who knows the scheme.
        abort_unless($secret !== '' && abs(now()->getTimestamp() - (int) $timestamp) <= 300 && hash_equals($expected, (string) $request->header('Steddle-Signature')), 403);

        $event = $request->json('event');
        abort_unless(in_array($event, ['updated', 'left', 'signed-out'], true), 422);

        $user = config('auth.providers.users.model')::query()->where('steddle_id', (string) $request->json('id'))->first();

        if ($user === null) {
            return response('');
        }

        match ($event) {
            'updated' => $user->fillFromSteddleAccount($request->json()->all())->save(),
            'left' => $this->leave($user),
            'signed-out' => $user->signOutEverywhere(),
        };

        return response('');
    }

    /** Its sessions first: a session row pointing at a deleted account stays in the table until it expires. */
    private function leave(Model $user): void
    {
        $user->signOutEverywhere();

        app(DeleteAccount::class)($user);
    }
}
