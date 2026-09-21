<x-layouts::site :title="'Lab | '.config('imprint.name')" description="The design system, the components and the experiments behind the site.">
    <x-site.page-title title="Lab." lead="The design system, every component the site renders, and the questions still open, one page each.">
        <div class="flex flex-col divide-y divide-zinc-200 border-y border-zinc-200 dark:divide-zinc-700 dark:border-zinc-700">
            <x-site.link-row :href="route('foundry.design')" title="Design">The brand, the colours, the type and the assets, on one page.</x-site.link-row>
            <x-site.link-row :href="route('foundry.components')" title="Components">Every component the site renders, live, with its Blade beside it.</x-site.link-row>
            @if (\Steddle\Foundry\Catalog\Catalog::custom() !== [])
                <x-site.link-row :href="route('foundry.components', ['custom' => 1])" title="Custom components">The components this site keeps outside the foundry.</x-site.link-row>
            @endif
            @foreach ($pages as $page => [$title, $summary])
                <x-site.link-row :href="route('foundry.lab', $page)" :title="$title">{{ $summary }}</x-site.link-row>
            @endforeach
        </div>
    </x-site.page-title>
</x-layouts::site>
