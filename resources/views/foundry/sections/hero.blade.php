@props(['scene' => null, 'align' => 'start', 'tall' => false, 'eyebrow' => null, 'title', 'marked' => null, 'lead' => null])

{{--
    The ink band a page opens on, under the header that overlays it. The
    eyebrow, the title and the lede come first, one gap apart; the actions
    and the slot follow at the same gap.

    @group Sections
    @prop scene The photo behind the band, by its name in config/imprint.php, which frames it; without one the band is ink and grain.
    @prop align start or center: where the content sets, and which scrim keeps it legible there.
    @prop tall Gives the band the screen's height, up to 56rem, and a display title; without it the content sets the height.
    @prop eyebrow A label in the accent above the title, or a slot where it is more than a label, a breadcrumb or a status.
    @prop title The page's h1, set at size 1, or display where the band is tall.
    @prop marked The phrase of `title` laid on the marker.
    @prop lead The lede under the title.
    @slot actions The buttons under the lede.
    @slot slot What follows the rest of the section, in the same band.

    @example A page
    @ground bare
    <foundry:sections.hero title="A page title." lead="The lead that says what the page is for, in a sentence or two." />

    @example Centred and tall
    @ground bare
    <foundry:sections.hero align="center" tall eyebrow="The eyebrow" title="The first thing a reader sees." marked="a reader sees." lead="A lede under it, centred.">
        <x-slot:actions>
            <foundry:button href="#">An action</foundry:button>
        </x-slot:actions>
    </foundry:sections.hero>
--}}
<section {{ $attributes->class(['relative isolate overflow-hidden bg-zinc-900 ink', 'grain' => ! $scene]) }}>
    @if ($scene)
        <foundry:scene :name="$scene" eager :scrim="$align" />
    @endif

    <foundry:container @class([
        'relative flex flex-col gap-6',
        'items-start' => $align === 'start',
        'items-center text-center' => $align === 'center',
        'pt-36 pb-16 lg:pt-44 lg:pb-24' => ! $tall,
        'min-h-[min(100svh,56rem)] justify-center pt-36 pb-24 lg:pb-32' => $tall,
    ])>
        <foundry:section-head stacked :$align :size="$tall ? 'display' : '1'" :level="1" :$eyebrow :$title :$marked :$lead />
        @isset($actions)
            <foundry:actions>{{ $actions }}</foundry:actions>
        @endisset
        {{ $slot }}
    </foundry:container>
</section>
