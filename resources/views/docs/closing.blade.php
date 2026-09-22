@use('Steddle\Foundry\Content')

{{-- Where to go when the docs did not answer, as `imprint.docs.closing` states it. --}}
@if (config('imprint.docs.closing'))
    <foundry:sections.closing align="start" :title="Content::copy('docs', 'closing.title')" :lead="Content::copy('docs', 'closing.lead')">
        <x-slot:actions>
            <foundry:button :href="url(config('imprint.docs.closing.url'))">{{ Content::copy('docs', 'closing.action') }}</foundry:button>
        </x-slot:actions>
    </foundry:sections.closing>
@endif
