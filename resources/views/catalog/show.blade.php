<x-layouts::site :title="$entry['name'].' | Components | '.config('imprint.name')" :description="$entry['description']">
    <x-site.section>
        <x-site.document>
            <x-slot:aside>
                <div class="flex flex-col gap-8">
                    <x-foundry::catalog.from :$from :$hasCustom />
                    <x-site.side-nav :groups="$groups" label="Components" />
                </div>
            </x-slot:aside>

            <div class="flex flex-col gap-12">
                {{-- The title and its lede open the column, so the index beside it starts under the bar. --}}
                <div class="flex flex-col gap-4">
                    <x-site.heading size="1" level="1">{{ $entry['name'] }}.</x-site.heading>
                    <x-site.text variant="lede" class="max-w-[60ch]">{{ $entry['description'] }}</x-site.text>
                </div>

                <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
                    <code class="font-mono text-code text-zinc-950 dark:text-zinc-50 slashed-zero tabular-nums">{{ $entry['tag'] ?? 'x-site.'.$slug }}</code>
                    @if ($own === 'copies')
                        <x-site.text variant="small" tone="strong" class="font-medium">✗ This imprint keeps a copy of its own, in place of the foundry's.</x-site.text>
                    @elseif ($own === 'wraps')
                        <x-site.text variant="small" tone="muted">From steddle/foundry, wrapped with this imprint's content.</x-site.text>
                    @elseif ($entry['from'] === 'custom')
                        <x-site.text variant="small" tone="muted">This imprint's own, outside steddle/foundry.{{ ($entry['examples'][0]['code'] ?? false) ? ' Add an @example to its opening comment to render it here.' : '' }}</x-site.text>
                    @elseif ($entry['from'] === 'foundry')
                        <x-site.text variant="small" tone="muted">From steddle/foundry.</x-site.text>
                    @else
                        <x-site.text variant="small" tone="muted">Every imprint supplies its own.</x-site.text>
                    @endif
                </div>

                @foreach ($entry['examples'] as $index => $example)
                    <x-foundry::catalog.example :$example :$slug :$index />
                @endforeach

                @foreach (['Props' => $entry['props'], 'Slots' => $entry['slots']] as $heading => $rows)
                    @if ($rows !== [])
                        <div class="flex flex-col gap-3">
                            <x-site.heading size="2" level="2">{{ $heading }}</x-site.heading>
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
                                                        <x-site.text variant="small" tone="muted">Required</x-site.text>
                                                    @else
                                                        <code class="font-mono text-code text-zinc-600 dark:text-zinc-400 slashed-zero">{{ $row['default'] }}</code>
                                                    @endif
                                                </flux:table.cell>
                                            @endif
                                            <flux:table.cell class="whitespace-normal align-top"><x-site.text variant="small">{{ $row['description'] }}</x-site.text></flux:table.cell>
                                        </flux:table.row>
                                    @endforeach
                                </flux:table.rows>
                            </flux:table>
                        </div>
                    @endif
                @endforeach
            </div>
        </x-site.document>
    </x-site.section>
</x-layouts::site>
