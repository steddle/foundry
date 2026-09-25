<?php

namespace Steddle\Foundry\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Passport;
use Laravel\Passport\Token;
use Livewire\Attributes\Locked;
use Steddle\Foundry\Mcp\Agents;

/**
 * For foundry:agent-connections. Needs laravel/passport.
 */
trait ManagesAgentConnections
{
    /**
     * @var list<array{id: string, name: string, hosts: string, connected_at: string}>
     */
    #[Locked]
    public array $agents = [];

    public function mountManagesAgentConnections(): void
    {
        $this->loadAgents();
    }

    /**
     * A client stays connected while it exists and holds a live access token,
     * or a live refresh token to mint one with.
     */
    public function loadAgents(): void
    {
        $this->agents = $this->agentTokens()
            ->where('revoked', false)
            ->where(fn (Builder $query) => $query
                ->where('expires_at', '>', now())
                ->orWhereHas('refreshToken', fn (Builder $refresh) => $refresh->where('revoked', false)->where('expires_at', '>', now())))
            ->has('client')
            ->with('client')
            ->latest()
            ->get()
            ->unique('client_id')
            ->map(fn (Token $token): array => [
                'id' => (string) $token->client_id,
                'name' => Agents::name($token->client->name),
                'hosts' => Agents::hosts($token->client->redirect_uris),
                'connected_at' => $token->created_at->diffForHumans(),
            ])
            ->values()
            ->all();
    }

    /**
     * Revokes every access token the client holds for the account, and the
     * refresh token behind each, so it cannot mint another.
     */
    public function disconnectAgent(string $id): void
    {
        $tokens = $this->agentTokens()->where('client_id', $id);

        Passport::refreshToken()->newQuery()->whereIn('access_token_id', $tokens->clone()->select('id'))->update(['revoked' => true]);
        $tokens->update(['revoked' => true]);

        $this->loadAgents();
    }

    /**
     * @return Builder<Token>
     */
    private function agentTokens(): Builder
    {
        return Passport::token()->newQuery()->where('user_id', Auth::id());
    }
}
