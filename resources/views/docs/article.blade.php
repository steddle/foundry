@use('Steddle\Foundry\Content')
@use('Steddle\Foundry\Outline')

@php
    $topics = Content::docs();
    $topic = $topics[$topicSlug];
    $article = $topic['articles'][$slug];
    [$title, $description] = $article;
    $slugs = array_keys($topic['articles']);
    $position = array_search($slug, $slugs);
    $docs = Content::copy('docs', 'title');
    ['html' => $body, 'headings' => $headings] = Outline::of(view(Content::view('docs.articles', $slug))->render());
    $nav = [
        __('foundry::docs.on_this_page') => collect($headings)->map(fn (string $label, string $id): array => ['label' => $label, 'href' => '#'.$id])->values()->all(),
        $topic['title'] => collect($topic['articles'])->map(fn (array $entry, string $key): array => [
            'label' => $entry[0],
            'href' => localized_route('docs.article.'.$key),
            'current' => $key === $slug,
        ])->values()->all(),
    ];
    $pager = array_filter([
        'previous' => ($previous = $slugs[$position - 1] ?? null) ? [$topic['articles'][$previous][0], localized_route('docs.article.'.$previous)] : null,
        'next' => ($next = $slugs[$position + 1] ?? null) ? [$topic['articles'][$next][0], localized_route('docs.article.'.$next)] : null,
    ]);
@endphp

<x-layouts::site :title="$title.' | '.$docs.' | '.config('imprint.name')" :description="$description">
    <x-slot:og>
        <x-site.og-image :heading="$title" :lede="$description" :eyebrow="$topic['title']" />
    </x-slot:og>

    <x-site.hero scene="docs-hero" :title="$title" :lead="$description">
        <x-slot:eyebrow>
            <x-site.breadcrumb :items="[$docs => localized_route('docs.index'), $topic['title'] => localized_route('docs.category.'.$topicSlug)]" />
        </x-slot:eyebrow>
        @if ($article['draft'] ?? false)
            <x-site.badge tone="warning">{{ __('foundry::docs.draft') }}</x-site.badge>
        @endif
    </x-site.hero>

    <x-site.numbered-section :number="sprintf('%02d', array_search($topicSlug, array_keys($topics)) + 1)" :name="$topic['title']" :note="__('foundry::docs.article_of', ['position' => $position + 1, 'total' => count($slugs)])">
        <x-slot:actions>
            <x-site.copy-menu />
        </x-slot:actions>
        <x-site.document>
            <x-slot:aside>
                <x-site.side-nav :groups="$nav" :label="__('foundry::docs.on_this_page')" />
            </x-slot:aside>

            <div class="flex flex-col gap-12">
                <article class="longform max-w-[70ch]">
                    {!! $body !!}
                </article>

                <x-site.pager :previous="$pager['previous'] ?? null" :next="$pager['next'] ?? null">{{ $article['checked'] ?? '' }}</x-site.pager>
            </div>
        </x-site.document>
    </x-site.numbered-section>

    <x-slot:closing>
        @include('foundry::docs.closing')
    </x-slot:closing>
</x-layouts::site>
