@props(['scene' => null])

{{--
    The ink band a record's page opens on, under the app bar: a scene, or ink
    and grain, with an x-site.page-head in it, which reads on ink as it does
    on bone. Sized for a working page, not a cover. Set it in an
    x-site.app-page with `flush`, the rest of the page in a container under it.

    @group Shell
    @prop scene The photo behind the band, by its name in config/imprint.php; without one the band is ink and grain.
    @slot slot The band's words, an x-site.page-head.

    @example A record opening on ink
    @ground bare
    <x-site.app-band>
        <x-site.page-head :breadcrumbs="['Dashboard' => '#']" eyebrow="Mutual NDA with Northwind B.V." title="Ready for your signature." lead="Between Ada Visser and Northwind B.V. 2 years, then 3 years confidential.">
            <x-slot:status><x-site.badge tone="action" dot>Awaiting your signature</x-site.badge></x-slot:status>
        </x-site.page-head>
    </x-site.app-band>
--}}
<section {{ $attributes->class(['relative isolate overflow-hidden bg-zinc-900 ink', 'grain' => ! $scene]) }}>
    @if ($scene)
        <x-site.scene :name="$scene" eager scrim="start" />
    @endif
    <x-site.container class="relative py-12 lg:py-16">
        {{ $slot }}
    </x-site.container>
</section>
