@props(['agents', 'url' => null, 'command' => null])

{{--
    How the signed-in reader connects an agent, on a foundry:panel: the MCP
    server's URL and the claude mcp add command, each to copy, then every
    OAuth client connected to the account, with where it receives its
    access and a button to disconnect it. The page's Livewire component uses
    Steddle\Foundry\Concerns\ManagesAgentConnections, which holds
    $agents and answers disconnectAgent.

    @group Forms
    @prop agents The component's `$agents`.
    @prop url The MCP server's URL; without one, `/mcp` on the imprint.
    @prop command The command that adds the server to Claude Code; without one, named after the imprint.

    @example With a connected agent
    <foundry:agent-connections :agents="[['id' => '9f2c', 'name' => 'Claude Code', 'hosts' => 'localhost', 'connected_at' => '2 days ago']]" url="https://bron.steddle.com/mcp" command="claude mcp add --transport http bron https://bron.steddle.com/mcp" class="max-w-3xl" />

    @example None yet
    <foundry:agent-connections :agents="[]" url="https://bron.steddle.com/mcp" command="claude mcp add --transport http bron https://bron.steddle.com/mcp" class="max-w-3xl" />
--}}
@php
    // Resolved here, not in @props: the catalogue and the skill read a default in @props as the machine's live URL.
    $url ??= \Steddle\Foundry\Mcp\Agents::url();
    $command ??= \Steddle\Foundry\Mcp\Agents::command();
@endphp
<foundry:panel :title="__('foundry::mcp.agents.title')" :lead="__('foundry::mcp.agents.lead', ['name' => config('imprint.name')])" {{ $attributes }}>
    <div class="flex flex-col gap-3">
        <foundry:text variant="label" tone="strong">{{ __('foundry::mcp.agents.url') }}</foundry:text>
        <flux:input :value="$url" :aria-label="__('foundry::mcp.agents.url')" readonly copyable />
    </div>

    <div class="flex flex-col gap-3">
        <foundry:text variant="label" tone="strong">{{ __('foundry::mcp.agents.command') }}</foundry:text>
        <foundry:command :$command :label="__('foundry::mcp.agents.command')" />
    </div>

    <div class="flex flex-col gap-3">
        <foundry:text variant="label" tone="strong">{{ __('foundry::mcp.agents.connected') }}</foundry:text>
        @if ($agents === [])
            <foundry:text variant="small" tone="muted">{{ __('foundry::mcp.agents.none') }}</foundry:text>
        @else
            <foundry:rows>
                @foreach ($agents as $agent)
                    <foundry:record-row wire:key="agent-{{ $agent['id'] }}" :title="$agent['name']" :meta="__('foundry::mcp.agents.since', ['time' => $agent['connected_at']]).' | '.$agent['hosts']">
                        <x-slot:actions>
                            <foundry:confirm-button :label="__('foundry::mcp.agents.disconnect')" size="sm" action="$wire.disconnectAgent({{ Js::from($agent['id']) }})" />
                        </x-slot:actions>
                    </foundry:record-row>
                @endforeach
            </foundry:rows>
        @endif
    </div>
</foundry:panel>
