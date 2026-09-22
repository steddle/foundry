<x-layouts::site :title="'Lab | '.config('imprint.name')" description="The design system, the components and the experiments behind the site.">
    <foundry:sections.page-title title="Lab." lead="The design system, every component the site renders, and the questions still open, one page each.">
        <div class="flex flex-col divide-y divide-zinc-200 border-y border-zinc-200 dark:divide-zinc-700 dark:border-zinc-700">
            <foundry:link-row :href="route('foundry.design')" title="Design">The brand, the colours, the type and the assets, on one page.</foundry:link-row>
            <foundry:link-row :href="route('foundry.components')" title="Components">Every component the site renders, live, with its Blade beside it.</foundry:link-row>
            @if (\Steddle\Foundry\Catalog\Catalog::custom() !== [])
                <foundry:link-row :href="route('foundry.components', ['from' => 'custom'])" title="Custom components">The components this site keeps outside the foundry.</foundry:link-row>
            @endif
            @foreach ($pages as $page => [$title, $summary])
                <foundry:link-row :href="route('foundry.lab', $page)" :title="$title">{{ $summary }}</foundry:link-row>
            @endforeach
        </div>
    </foundry:sections.page-title>
</x-layouts::site>
