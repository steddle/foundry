{{-- A long document as an imprint prints one: a cover, then its markdown a part to a page. --}}
<foundry:print.document title="Where we stand" footer="Where we stand | September 2026 | Confidential, internal">
    <foundry:print.cover eyebrow="Internal briefing" title="Where we stand" lead="The company, the register and the first matter.">
        <foundry:print.facts :items="['Prepared for' => 'The Steddle team', 'Prepared by' => 'Mischa Sigtermans', 'Date' => '23 September 2026', 'Basis' => 'The register on 23 September 2026']" />
        <x-slot:foot>
            <span>Confidential, internal</span>
            <span>Steddle B.V. | Amsterdam | 2026</span>
        </x-slot:foot>
    </foundry:print.cover>

    <foundry:print.prose new-page>
        {!! $body !!}
    </foundry:print.prose>
</foundry:print.document>
