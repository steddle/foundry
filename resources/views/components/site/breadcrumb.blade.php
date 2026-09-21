@props(['items'])

{{-- $items: label => href, outermost first. The page's own title stands under it, so the trail names only what is above. --}}
<flux:breadcrumbs {{ $attributes }}>
    @foreach ($items as $label => $href)
        <flux:breadcrumbs.item :href="$href">{{ $label }}</flux:breadcrumbs.item>
    @endforeach
</flux:breadcrumbs>
