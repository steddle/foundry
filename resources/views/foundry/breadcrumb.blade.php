@props(['items'])

{{--
    The trail of pages above the current one, drawn by Flux. The page's own
    title stands under it, so the trail names only what is above.

    @group Navigation
    @prop items label => href for each page above this one, outermost first.

    @example Two levels up
    <foundry:breadcrumb :items="['Docs' => '#', 'Getting started' => '#']" />
--}}
<flux:breadcrumbs {{ $attributes }}>
    @foreach ($items as $label => $href)
        <flux:breadcrumbs.item :href="$href">{{ $label }}</flux:breadcrumbs.item>
    @endforeach
</flux:breadcrumbs>
