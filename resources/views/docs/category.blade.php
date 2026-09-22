@use('Steddle\Foundry\Content')

@php
    $topics = Content::docs();
    $topic = $topics[$slug];
    $docs = Content::copy('docs', 'title');
    $nav = [__('foundry::docs.topics') => collect($topics)->map(fn (array $entry, string $key): array => [
        'label' => $entry['title'],
        'href' => localized_route('docs.category.'.$key),
        'current' => $key === $slug,
    ])->values()->all()];
@endphp

<x-layouts::site :title="$topic['title'].' | '.$docs.' | '.config('imprint.name')" :description="$topic['description']">
    <x-slot:og>
        <x-site.og-image :heading="$topic['title']" :lede="$topic['description']" :eyebrow="$docs" />
    </x-slot:og>

    <x-site.sections.hero scene="docs-hero" :title="$topic['title'].'.'" :lead="$topic['description']">
        <x-slot:eyebrow>
            <x-site.breadcrumb :items="[$docs => localized_route('docs.index')]" />
        </x-slot:eyebrow>
    </x-site.sections.hero>

    <x-site.numbered-section :number="sprintf('%02d', array_search($slug, array_keys($topics)) + 1)" :name="$topic['title']" :note="trans_choice('foundry::docs.articles', count($topic['articles']))">
        <x-slot:actions>
            <x-site.copy-menu />
        </x-slot:actions>
        <x-site.document>
            <x-slot:aside>
                <x-site.side-nav :groups="$nav" :label="__('foundry::docs.all_topics')" />
            </x-slot:aside>

            <div class="flex flex-col divide-y divide-zinc-200 dark:divide-zinc-700 border-y border-zinc-200 dark:border-zinc-700">
                @foreach ($topic['articles'] as $articleSlug => [$title, $description])
                    <x-site.link-row :href="localized_route('docs.article.'.$articleSlug)" :title="$title">
                        {{ $description }}
                    </x-site.link-row>
                @endforeach
            </div>
        </x-site.document>
    </x-site.numbered-section>

    <x-slot:closing>
        @include('foundry::docs.closing')
    </x-slot:closing>
</x-layouts::site>
