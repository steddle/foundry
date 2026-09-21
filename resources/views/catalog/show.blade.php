<x-layouts::site :title="$entry['name'].' | Components | '.config('imprint.name')" :description="$entry['description']">
    <x-site.page-title :title="$entry['name'].'.'" :lead="$entry['description']">
        <x-site.document>
            <x-slot:aside>
                <x-site.side-nav :groups="$groups" label="Components" />
            </x-slot:aside>

            <div class="flex flex-col gap-12">
                <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
                    <code class="font-mono text-code text-zinc-950 dark:text-zinc-50 slashed-zero tabular-nums">x-site.{{ $slug }}</code>
                    @if ($own === 'copies')
                        <x-site.text variant="small" tone="strong" class="font-medium">✗ This imprint keeps a copy of its own, in place of the foundry's.</x-site.text>
                    @elseif ($own === 'wraps')
                        <x-site.text variant="small" tone="muted">From steddle/foundry, wrapped with this imprint's content.</x-site.text>
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
    </x-site.page-title>
</x-layouts::site>
