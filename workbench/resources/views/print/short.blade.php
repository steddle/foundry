{{-- A short document as an imprint prints one: the masthead, then its markdown. --}}
<foundry:print.document title="Response to the DBT consultation" footer="Steddle B.V. (in formation) | Response to the DBT consultation">
    <foundry:print.masthead eyebrow="Response to consultation" title="Swifter and simpler competition redress" lead="Take-up, trust, and the quality of the claimant register at certification.">
        <foundry:print.facts :items="['Submitted to' => 'Department for Business and Trade', 'Respondent' => 'Steddle B.V. (in formation), Amsterdam']" class="mt-8" />
    </foundry:print.masthead>

    <foundry:print.prose class="mt-9">
        {!! $body !!}
    </foundry:print.prose>
</foundry:print.document>
