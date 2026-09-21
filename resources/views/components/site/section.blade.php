@props(['scene' => null, 'align' => 'start'])

{{--
    A band of the page. In dark mode every band shares the page ground, so a
    hairline separates them. With a `scene` the band is ink over that photo,
    its content at the start or in the centre as `align` says, under the scrim
    measured for it.
--}}
<section {{ $attributes->class([
    'scroll-mt-4 py-18 lg:py-32',
    'dark:border-t dark:border-zinc-50/13' => ! $scene,
    'relative isolate overflow-hidden bg-zinc-900 ink' => $scene,
]) }}>
    @if ($scene)
        <x-site.scene :name="$scene" :scrim="$align" />
    @endif
    <x-site.container @class(['relative flex flex-col gap-12 lg:gap-18', 'items-center text-center' => $align === 'center'])>
        {{ $slot }}
    </x-site.container>
</section>
