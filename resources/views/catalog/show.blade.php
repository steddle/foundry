<x-layouts::site :title="$entry['name'].' | Components | '.config('imprint.name')" :description="$entry['description']">
    <foundry:section>
        <foundry:document>
            <x-slot:aside>
                <div class="flex flex-col gap-8">
                    <x-foundry::catalog.from :$from :$hasCustom />
                    <foundry:side-nav :groups="$groups" label="Components" />
                </div>
            </x-slot:aside>

            <div class="flex flex-col gap-12">
                {{-- The title and its lede open the column, so the index beside it starts under the bar. --}}
                <div class="flex flex-col gap-4">
                    <foundry:heading size="1" level="1">{{ $entry['name'] }}.</foundry:heading>
                    <foundry:text variant="lede" class="max-w-[60ch]">{{ $entry['description'] }}</foundry:text>
                </div>

                <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
                    <code class="font-mono text-code text-zinc-950 dark:text-zinc-50 slashed-zero tabular-nums">{{ $entry['tag'] ?? 'foundry:'.$slug }}</code>
                    @if ($own)
                        <foundry:text variant="small" tone="strong" class="font-medium">✗ This imprint keeps a file of its own, in place of the foundry's.</foundry:text>
                    @elseif ($entry['from'] === 'custom')
                        <foundry:text variant="small" tone="muted">This imprint's own, outside steddle/foundry.{{ ($entry['examples'][0]['code'] ?? false) ? ' Add an @example to its opening comment to render it here.' : '' }}</foundry:text>
                    @elseif ($entry['from'] === 'foundry')
                        <foundry:text variant="small" tone="muted">From steddle/foundry.</foundry:text>
                    @else
                        <foundry:text variant="small" tone="muted">Every imprint supplies its own.</foundry:text>
                    @endif
                </div>

                @foreach ($entry['examples'] as $index => $example)
                    <x-foundry::catalog.example :$example :$slug :$index />
                @endforeach

                @foreach (['Props' => $entry['props'], 'Slots' => $entry['slots']] as $heading => $rows)
                    @if ($rows !== [])
                        <div class="flex flex-col gap-3">
                            <foundry:heading size="2" level="2">{{ $heading }}</foundry:heading>
                            <flux:table>
                                <flux:table.columns>
                                    <flux:table.column>Name</flux:table.column>
                                    @if ($heading === 'Props')
                                        <flux:table.column>Default</flux:table.column>
                                    @endif
                                    <flux:table.column>Description</flux:table.column>
                                </flux:table.columns>
                                <flux:table.rows>
                                    @foreach ($rows as $row)
                                        <flux:table.row>
                                            <flux:table.cell class="align-top"><code class="font-mono text-code text-zinc-950 dark:text-zinc-50 slashed-zero">{{ $row['name'] }}</code></flux:table.cell>
                                            @if ($heading === 'Props')
                                                <flux:table.cell class="align-top">
                                                    @if ($row['default'] === null)
                                                        <foundry:text variant="small" tone="muted">Required</foundry:text>
                                                    @elseif ($row['default'] === 'passed on')
                                                        <foundry:text variant="small" tone="muted">Passed on</foundry:text>
                                                    @else
                                                        <code class="font-mono text-code text-zinc-600 dark:text-zinc-400 slashed-zero">{{ $row['default'] }}</code>
                                                    @endif
                                                </flux:table.cell>
                                            @endif
                                            <flux:table.cell class="whitespace-normal align-top"><foundry:text variant="small">{{ $row['description'] }}</foundry:text></flux:table.cell>
                                        </flux:table.row>
                                    @endforeach
                                </flux:table.rows>
                            </flux:table>
                        </div>
                    @endif
                @endforeach
            </div>
        </foundry:document>
    </foundry:section>
</x-layouts::site>
