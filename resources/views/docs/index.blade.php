@use('Steddle\Foundry\Content')

@php
    $topics = Content::docs();
    $start = array_values($topics)[0]['articles'] ?? [];
    $startTopic = array_key_first($topics);
    $articleCount = collect($topics)->sum(fn (array $topic): int => count($topic['articles']));
    $title = Content::copy('docs', 'title');
@endphp

<x-layouts::site :title="$title.' | '.config('imprint.name')" :description="Content::copy('docs', 'description')">
    <foundry:sections.hero scene="docs-hero" :title="$title.'.'" :lead="Content::copy('docs', 'lead')" />

    <foundry:numbered-section number="01" :name="__('foundry::docs.start_here')" :note="trans_choice('foundry::docs.articles', count($start))">
        <x-slot:actions>
            <foundry:copy-menu />
        </x-slot:actions>
        <div class="grid grid-cols-1 gap-x-12 border-t border-zinc-200 dark:border-zinc-700 lg:grid-cols-2">
            @foreach ($start as $slug => [$articleTitle, $description])
                <foundry:link-row :href="localized_route('docs.article.'.$slug)" :title="$articleTitle" class="border-b border-zinc-200 dark:border-zinc-700">
                    {{ $description }}
                </foundry:link-row>
            @endforeach
        </div>
    </foundry:numbered-section>

    <foundry:numbered-section number="02" :name="__('foundry::docs.topics')" :note="trans_choice('foundry::docs.topics_count', count($topics)).' | '.trans_choice('foundry::docs.articles', $articleCount)" sunken>
        <foundry:tile-grid :count="count($topics)">
            @foreach ($topics as $slug => $topic)
                <foundry:topic :icon="$topic['icon']" :eyebrow="trans_choice('foundry::docs.articles', count($topic['articles']))" :title="$topic['title']" :href="localized_route('docs.category.'.$slug)"
                    :links="collect($topic['articles'])->take(4)->mapWithKeys(fn (array $article, string $articleSlug): array => [$article[0] => localized_route('docs.article.'.$articleSlug)])->all()"
                    :more="count($topic['articles']) > 4 ? __('foundry::docs.all_articles', ['count' => count($topic['articles'])]) : null">
                    {{ $topic['description'] }}
                </foundry:topic>
            @endforeach
        </foundry:tile-grid>
    </foundry:numbered-section>

    <x-slot:closing>
        @include('foundry::docs.closing')
    </x-slot:closing>
</x-layouts::site>
