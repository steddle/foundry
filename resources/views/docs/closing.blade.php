@use('Steddle\Foundry\Content')

{{-- Where to go when the docs did not answer, as `imprint.docs.closing` states it. --}}
@if (config('imprint.docs.closing'))
    <x-site.closing align="start" :title="Content::copy('docs', 'closing.title')" :lead="Content::copy('docs', 'closing.lead')">
        <x-site.button :href="url(config('imprint.docs.closing.url'))">{{ Content::copy('docs', 'closing.action') }}</x-site.button>
    </x-site.closing>
@endif
