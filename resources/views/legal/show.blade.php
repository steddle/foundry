@use('Steddle\Foundry\Content')
@use('Steddle\Foundry\Outline')

@php
    $legal = Content::legal();
    $audienceKey = collect($legal['audiences'])->search(fn (array $audience): bool => isset($audience['documents'][$document]));
    $audience = $legal['audiences'][$audienceKey];
    [$title, $description] = $audience['documents'][$document];
    $all = collect($legal['audiences'])->flatMap(fn (array $audience): array => $audience['documents']);
    $position = $all->keys()->search($document);
    $legalTitle = Content::copy('legal', 'title');
    ['html' => $body, 'headings' => $headings] = Outline::of(view(Content::view('legal.documents', $document))->render());
    $nav = [
        __('foundry::legal.on_this_page') => Outline::links($headings),
        __('foundry::legal.every_document') => $all->map(fn (array $entry, string $key): array => [
            'label' => $entry[0],
            'href' => localized_route('legal.show', $key),
            'current' => $key === $document,
        ])->values()->all(),
    ];
@endphp

<x-layouts::site :title="$title.' | '.$legalTitle.' | '.config('imprint.name')" :description="$description">
    <x-slot:og>
        <x-site.og-image :heading="$title" :lede="$description" :eyebrow="$legalTitle" />
    </x-slot:og>

    <x-site.hero scene="legal-hero" :title="$title" :lead="$description">
        <x-slot:eyebrow>
            <x-site.breadcrumb :items="[$legalTitle => localized_route('legal.index')]" />
        </x-slot:eyebrow>
        @if (config('imprint.legal.draft'))
            <x-site.badge tone="warning">{{ Content::copy('legal', 'draft') }}</x-site.badge>
        @endif
    </x-site.hero>

    <x-site.numbered-section :number="sprintf('%02d', array_search($audienceKey, array_keys($legal['audiences'])) + 2)" :name="$audience['for']" :note="__('foundry::legal.document_of', ['position' => $position + 1, 'total' => $all->count()])">
        <x-slot:actions>
            <x-site.copy-menu />
        </x-slot:actions>
        <x-site.document>
            <x-slot:aside>
                <x-site.side-nav :groups="$nav" :label="__('foundry::legal.on_this_page')" />
            </x-slot:aside>

            <div class="flex flex-col gap-12">
                <article class="longform max-w-[70ch]">
                    {!! $body !!}
                </article>

                <x-site.pager :back="[__('foundry::legal.every_document'), localized_route('legal.index')]">
                    @if (config('imprint.legal.contact'))
                        {{ __('foundry::legal.questions') }} <a href="mailto:{{ config('imprint.legal.contact') }}" class="text-primary-700 dark:text-primary-300 hover:text-primary-800 dark:hover:text-primary-200">{{ config('imprint.legal.contact') }}</a>
                    @endif
                </x-site.pager>
            </div>
        </x-site.document>
    </x-site.numbered-section>
</x-layouts::site>
