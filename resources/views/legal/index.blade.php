@use('Steddle\Foundry\Content')

@php
    $legal = Content::legal();
    $title = Content::copy('legal', 'title');
@endphp

<x-layouts::site :title="$title.' | '.config('imprint.name')" :description="Content::copy('legal', 'description')">
    <x-site.hero scene="legal-hero" :title="$title.'.'" :lead="Content::copy('legal', 'lead')">
        @if (config('imprint.legal.draft'))
            <x-site.badge tone="warning">{{ Content::copy('legal', 'draft') }}</x-site.badge>
        @endif
    </x-site.hero>

    <x-site.numbered-section number="01" :name="__('foundry::legal.what_we_hold_to')" :note="trans_choice('foundry::legal.promises', count($legal['promises']))">
        <x-slot:actions>
            <x-site.copy-menu />
        </x-slot:actions>
        <x-site.promises>
            @foreach ($legal['promises'] as [$promise, $body])
                <x-site.promises.item :title="$promise">{{ $body }}</x-site.promises.item>
            @endforeach
        </x-site.promises>
    </x-site.numbered-section>

    @foreach ($legal['audiences'] as $audience)
        <x-site.numbered-section :number="sprintf('%02d', $loop->index + 2)" :name="$audience['title']" :note="trans_choice('foundry::legal.documents', count($audience['documents']))" @class(['bg-zinc-100 dark:bg-zinc-950' => $loop->odd])>
            <div class="grid grid-cols-1 gap-x-12 border-t border-zinc-200 dark:border-zinc-700 lg:grid-cols-2">
                @foreach ($audience['documents'] as $slug => [$documentTitle, $description])
                    <x-site.link-row :href="localized_route('legal.show', $slug)" :title="$documentTitle" class="border-b border-zinc-200 dark:border-zinc-700">
                        {{ $description }}
                    </x-site.link-row>
                @endforeach
            </div>
        </x-site.numbered-section>
    @endforeach
</x-layouts::site>
