@props(['scene' => null, 'align' => 'start', 'sunken' => false])

{{--
    A band of the page. In dark mode every band shares the page ground, so a
    hairline separates bands without a scene.

    @group Layout
    @prop scene The scene the band is ink over, by its name in config/imprint.php; without one the band sits on the page ground.
    @prop align start or center: where the content sets, and with a `scene` which measured scrim lies under it.
    @prop sunken Sets a band without a scene a step below the page, to part it from the bands around it.

    @example A band with a heading and a lede
    @ground bare
    <x-site.section>
        <x-site.heading size="2">A section heading.</x-site.heading>
        <x-site.text variant="lede">The lede under it, in the body colour.</x-site.text>
    </x-site.section>
--}}
<section {{ $attributes->class([
    'scroll-mt-4 py-18 lg:py-32',
    'dark:border-t dark:border-zinc-50/13' => ! $scene,
    'bg-zinc-100 dark:bg-zinc-950' => $sunken && ! $scene,
    'relative isolate overflow-hidden bg-zinc-900 ink' => $scene,
]) }}>
    @if ($scene)
        <x-site.scene :name="$scene" :scrim="$align" />
    @endif
    <x-site.container @class(['relative flex flex-col gap-12 lg:gap-18', 'items-center text-center' => $align === 'center'])>
        {{ $slot }}
    </x-site.container>
</section>
