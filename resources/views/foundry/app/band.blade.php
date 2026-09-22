@props(['scene' => null])

{{--
    The ink band a record's page opens on, with the app bar lying over it: a
    scene, or ink and grain, with an foundry:page-head in it, which reads on
    ink as it does on bone. Sized for a working page, not a cover, with room
    at the top for the bar. Set it in an foundry:layouts.app with `flush`, the
    rest of the page in a container under it.

    @group Shell
    @prop scene The photo behind the band, by its name in config/imprint.php; without one the band is ink and grain.
    @slot slot The band's words, an foundry:page-head.

    @example A record opening on ink
    @ground bare
    <foundry:app.band>
        <foundry:page-head :breadcrumbs="['Dashboard' => '#']" eyebrow="Mutual NDA with Northwind B.V." title="Ready for your signature." lead="Between Ada Visser and Northwind B.V. 2 years, then 3 years confidential.">
            <x-slot:status><foundry:badge tone="action" dot>Awaiting your signature</foundry:badge></x-slot:status>
        </foundry:page-head>
    </foundry:app.band>
--}}
<section {{ $attributes->class(['relative isolate overflow-hidden bg-zinc-900 ink', 'grain' => ! $scene]) }}>
    @if ($scene)
        <foundry:scene :name="$scene" eager scrim="start" />
    @endif
    <foundry:container class="relative pt-26 pb-12 lg:pt-30 lg:pb-16">
        {{ $slot }}
    </foundry:container>
</section>
