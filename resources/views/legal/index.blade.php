@use('Steddle\Foundry\Content')

@php
    $legal = Content::legal();
    $title = Content::copy('legal', 'title');
@endphp

<x-layouts::site :title="$title.' | '.config('imprint.name')" :description="Content::copy('legal', 'description')">
    <foundry:sections.hero scene="legal-hero" :title="$title.'.'" :lead="Content::copy('legal', 'lead')">
        @if (config('imprint.legal.draft'))
            <foundry:badge tone="warning">{{ Content::copy('legal', 'draft') }}</foundry:badge>
        @endif
    </foundry:sections.hero>

    <foundry:numbered-section number="01" :name="__('foundry::legal.what_we_hold_to')" :note="trans_choice('foundry::legal.promises', count($legal['promises']))">
        <x-slot:actions>
            <foundry:copy-menu />
        </x-slot:actions>
        <foundry:promises>
            @foreach ($legal['promises'] as [$promise, $body])
                <foundry:promises.item :title="$promise">{{ $body }}</foundry:promises.item>
            @endforeach
        </foundry:promises>
    </foundry:numbered-section>

    @foreach ($legal['audiences'] as $audience)
        <foundry:numbered-section :number="sprintf('%02d', $loop->index + 2)" :name="$audience['title']" :note="trans_choice('foundry::legal.documents', count($audience['documents']))" :sunken="$loop->odd">
            <div class="grid grid-cols-1 gap-x-12 border-t border-zinc-200 dark:border-zinc-700 lg:grid-cols-2">
                @foreach ($audience['documents'] as $slug => [$documentTitle, $description])
                    <foundry:link-row :href="localized_route('legal.show', $slug)" :title="$documentTitle" class="border-b border-zinc-200 dark:border-zinc-700">
                        {{ $description }}
                    </foundry:link-row>
                @endforeach
            </div>
        </foundry:numbered-section>
    @endforeach
</x-layouts::site>
