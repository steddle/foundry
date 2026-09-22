<x-layouts::site :title="'Components | '.config('imprint.name')" description="Every component the site renders, by group, each with its examples, its props and its slots.">
    <x-site.section>
        <x-site.document>
            <x-slot:aside>
                <div class="flex flex-col gap-8">
                    <x-foundry::catalog.from :$from :$hasCustom />
                    <x-site.side-nav :groups="$groups" label="Components" />
                </div>
            </x-slot:aside>

            <div class="flex flex-col gap-16">
                <div class="flex flex-col gap-4">
                    <x-site.heading size="1" level="1">Components.</x-site.heading>
                    <x-site.text variant="lede" class="max-w-[60ch]">Every component the site renders, by group. Each page shows it live, its Blade, its props and its slots.</x-site.text>
                </div>

                @foreach ($index as $group => $slugs)
                    <div class="flex flex-col gap-4">
                        <x-site.heading size="2" level="2">{{ $group }}</x-site.heading>
                        <x-site.tile-grid :count="count($slugs)">
                            @foreach ($slugs as $slug)
                                <a href="{{ route('foundry.components', [$slug, ...$query]) }}" class="group row-span-3 grid grid-rows-subgrid gap-2 bg-zinc-25 p-6 dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700/40">
                                    <x-site.heading size="3" level="3" class="group-hover:text-primary-700 dark:group-hover:text-primary-300">{{ $entries[$slug]['name'] }}</x-site.heading>
                                    <x-site.text variant="small" tone="muted" class="line-clamp-3">{{ $entries[$slug]['description'] }}</x-site.text>
                                    <code class="pt-2 font-mono text-code text-zinc-600 dark:text-zinc-400 slashed-zero">{{ $entries[$slug]['tag'] ?? 'x-site.'.$slug }}</code>
                                </a>
                            @endforeach
                        </x-site.tile-grid>
                    </div>
                @endforeach
            </div>
        </x-site.document>
    </x-site.section>
</x-layouts::site>
