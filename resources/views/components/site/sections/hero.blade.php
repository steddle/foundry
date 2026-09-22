@props(['scene' => null, 'align' => 'start', 'tall' => false, 'eager' => true, 'eyebrow' => null, 'title' => null, 'marked' => null, 'lead' => null])

{{--
    The ink band a page opens on, under the header that overlays it. The
    eyebrow, the title and the lede come first, one gap apart; the `actions`
    and the slot follow at the same gap.

    @group Sections
    @prop scene The photo behind the band, by its name in config/imprint.php, which frames it; without one the band is ink and grain.
    @prop align start or center: where the content sets, and which scrim keeps it legible there.
    @prop tall Gives the band the screen's height, up to 56rem, and a display title; without it the content sets the height.
    @prop eager Loads the scene's photo first, with high fetch priority, rather than lazily. On by default, since the hero opens the page.
    @prop eyebrow A label in the accent above the title, or a slot where it is more than a label, a breadcrumb or a status.
    @prop title The page's h1, set at size 1, or display where the band is tall.
    @prop marked The phrase of `title` laid on the marker.
    @prop lead The lede under the title.
    @slot actions The buttons under the lede.

    @example A page
    @ground bare
    <x-site.sections.hero title="A page title." lead="The lead that says what the page is for, in a sentence or two." />

    @example Centred and tall
    @ground bare
    <x-site.sections.hero align="center" tall eyebrow="The eyebrow" title="The first thing a reader sees." marked="a reader sees." lead="A lede under it, centred.">
        <x-slot:actions>
            <x-site.button href="#">An action</x-site.button>
        </x-slot:actions>
    </x-site.sections.hero>
--}}
<section {{ $attributes->class(['relative isolate overflow-hidden bg-zinc-900 ink', 'grain' => ! $scene]) }}>
    @if ($scene)
        <x-site.scene :name="$scene" :eager="$eager" :scrim="$align" />
    @endif

    <x-site.container @class([
        'relative flex flex-col',
        'items-start' => $align === 'start',
        'items-center text-center' => $align === 'center',
        'gap-6',
        'pt-36 pb-16 lg:pt-44 lg:pb-24' => ! $tall,
        'min-h-[min(100svh,56rem)] justify-center pt-36 pb-24 lg:pb-32' => $tall,
    ])>
        @if ($eyebrow instanceof \Illuminate\View\ComponentSlot)
            {{ $eyebrow }}
        @elseif ($eyebrow)
            <x-site.text variant="label" tone="accent">{{ $eyebrow }}</x-site.text>
        @endif
        @if ($title)
            <x-site.heading :size="$tall ? 'display' : '1'" level="1" @class(['max-w-[16ch]' => $tall, 'max-w-[20ch]' => ! $tall])><x-site.marker :text="$title" :marked="$marked" /></x-site.heading>
        @endif
        @if ($lead)
            <x-site.text variant="lede" class="max-w-[48ch]">{{ $lead }}</x-site.text>
        @endif
        @isset($actions)
            <x-site.actions>{{ $actions }}</x-site.actions>
        @endisset
        {{ $slot }}
    </x-site.container>
</section>
