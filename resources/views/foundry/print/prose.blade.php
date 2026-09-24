@props(['newPage' => false])

{{--
    A markdown body in print, inside foundry:print.document: Spectral at
    10.2pt, each h2 under the bar foundry:print.section sets, h3 and h4 in
    Chivo, a quotation on a lichen rule, and code, and only code, in Chivo
    Mono. A paragraph leaves three lines on either side of a break. A table
    stays on the page of the line that introduces it, repeats its head on
    every page it runs onto, and sets a column markdown aligns right, `--:`,
    flush right; its figures are tabular throughout.

    @prop newPage Starts every h2 on a page of its own, for a long document read part by part.
    @slot slot The rendered markdown, such as `Str::markdown($body)`.

    @example A briefing's body
    <foundry:print.prose>
        <h2>Where we stand</h2>
        <p>Steddle sells the claimant side of a collective action as a <strong>running service</strong>: find the case, build the platform, book the claimants.</p>
        <h3>The register</h3>
        <p>Every claimant registers on our platform, under our terms, and the register is checked rather than counted.</p>
        <blockquote><p>Take-up is the measure, not notice.</p></blockquote>
        <p>The first matter, by the numbers:</p>
        <table>
            <thead><tr><th>Measure</th><th align="right">Count</th></tr></thead>
            <tbody>
                <tr><td>Participants</td><td align="right">8.176</td></tr>
                <tr><td>Vehicles</td><td align="right">5.707</td></tr>
            </tbody>
        </table>
        <p>The register exports as <code>claimants.csv</code>.</p>
    </foundry:print.prose>
--}}
<div {{ $attributes->class('print-prose') }} @if ($newPage) data-new-page @endif>
    {{ $slot }}
</div>
