@props(['command', 'label' => null])

{{--
    A command a reader pastes into a terminal, on ink in both themes, in
    mono, with Flux's copy button at its end.

    @group Elements
    @prop command The command, without its prompt.
    @prop label The field's accessible name, where no label beside it names it.

    @example Adding an MCP server
    <foundry:command command="claude mcp add --transport http bron https://bron.steddle.com/mcp" label="Claude Code" class="max-w-xl" />
--}}
<div {{ $attributes->class('ink rounded-lg bg-zinc-900 p-1.5') }}>
    <flux:input.group>
        <flux:input.group.prefix class="font-mono text-code text-zinc-400!" aria-hidden="true">$</flux:input.group.prefix>
        <flux:input :value="$command" :aria-label="$label" readonly copyable class:input="font-mono text-code slashed-zero tabular-nums" />
    </flux:input.group>
</div>
