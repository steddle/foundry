<x-layouts::site :title="$entry['name'].' | Components | '.config('imprint.name')" :description="$entry['description']">
    <x-site.section>
        <x-site.document>
            <x-slot:aside>
                <div class="flex flex-col gap-8">
                    @if ($hasCustom)
                        <flux:button size="sm" :href="$custom ? route('foundry.components') : route('foundry.components', ['custom' => 1])" class="self-start">
                            {{ $custom ? 'All components' : 'Custom components' }}
                        </flux:button>
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
                    <code class="font-mono text-code text-zinc-950 dark:text-zinc-50 slashed-zero tabular-nums">x-site.{{ $slug }}</code>
                    @if ($own === 'copies')
                        <x-site.text variant="small" tone="strong" class="font-medium">✗ This imprint keeps a copy of its own, in place of the foundry's.</x-site.text>
                    @elseif ($own === 'wraps')
                        <x-site.text variant="small" tone="muted">From steddle/foundry, wrapped with this imprint's content.</x-site.text>
                    @elseif ($entry['from'] === 'custom')
                        <x-site.text variant="small" tone="muted">This imprint's own, outside steddle/foundry.</x-site.text>
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
