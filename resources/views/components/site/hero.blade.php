@props(['scene' => null, 'align' => 'start', 'tall' => false, 'eager' => true, 'eyebrow' => null, 'title' => null, 'marked' => null, 'lead' => null])

{{--
    The ink band a page opens on, under the header that overlays it. `scene`
    names its photo, framed as config/imprint.php states; without one the band
    is ink and grain. `align` sets the content at the start or in the centre and
    chooses the scrim that keeps it legible there. `tall` gives the band the
    screen's height, up to 56rem, and a display title; without it the content
    sets the height. The eyebrow, the title with its `marked` phrase and the
    lede come first, one gap apart; the slot follows at the same gap. An
    eyebrow that is more than a label, a breadcrumb or a status, is a slot.
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
            <x-site.heading :size="$tall ? 'display' : '1'" level="1" @class(['max-w-[16ch]' => $tall, 'max-w-[20ch]' => ! $tall])><x-site.marked :text="$title" :marked="$marked" /></x-site.heading>
        @endif
        @if ($lead)
            <x-site.text variant="lede" class="max-w-[48ch]">{{ $lead }}</x-site.text>
        @endif
        {{ $slot }}
    </x-site.container>
</section>
