<x-layouts::site :title="$entry['name'].' | Components | '.config('imprint.name')" :description="$entry['description']">
    <x-site.section>
        <x-site.document>
            <x-slot:aside>
                <div class="flex flex-col gap-8">
                    @if ($hasCustom)
                        <nav aria-label="Which components" class="flex self-start rounded-md border border-zinc-200 dark:border-zinc-700 p-0.5 text-small font-medium">
                            @foreach ([null => 'All', 'foundry' => 'Foundry', 'custom' => 'Custom'] as $value => $label)
                                <a href="{{ route('foundry.components', $value ? ['from' => $value] : []) }}" @if ($from === ($value ?: null)) aria-current="page" @endif @class([
                                    'rounded-sm px-3 py-1',
                                    'bg-zinc-200 dark:bg-zinc-700 text-zinc-950 dark:text-zinc-50' => $from === ($value ?: null),
                                    'text-zinc-600 dark:text-zinc-400 hover:text-zinc-950 dark:hover:text-zinc-50' => $from !== ($value ?: null),
                                ])>{{ $label }}</a>
                            @endforeach
                        </nav>
                    @endif
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

                @foreach ($entry['examples'] as $example)
                    <x-foundry::catalog.example :example="$example" />
                @endforeach
            </div>
        </x-site.document>
    </x-site.section>
</x-layouts::site>
