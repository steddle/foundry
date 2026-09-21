@use('Steddle\Foundry\Brand\BrandAssets')

@php
    $manifest = BrandAssets::manifest();
@endphp

{{-- The images foundry:assets renders, as each imprint's /design shows them, in pairs of one height: OG and social preview, light and dark banner. --}}
<div {{ $attributes->class('flex flex-col gap-8') }}>
    <x-site.text tone="muted" class="max-w-[64ch] hyphens-manual">
        Rendered from config/imprint.php by <code class="font-mono text-code text-strong slashed-zero tabular-nums">php artisan foundry:assets</code>. og-image.png stands in where OG Kit has no key; with one, ogkit.dev renders each page's own template.
    </x-site.text>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        @foreach (BrandAssets::all() as $asset)
            <figure class="flex flex-col gap-3 rounded-md border border-subtle bg-card p-4">
                <img src="{{ asset($asset->path) }}?v={{ substr($manifest[$asset->name] ?? '', 0, 8) }}" alt="{{ basename($asset->path) }}" width="{{ $asset->width }}" height="{{ $asset->height }}" class="h-auto w-full rounded-sm border border-subtle" loading="lazy">
                <figcaption class="flex flex-col gap-1">
                    <x-site.text variant="small" tone="strong" class="font-medium tabular-nums">{{ basename($asset->path) }} | {{ $asset->width }}×{{ $asset->height }}</x-site.text>
                    <x-site.text variant="small" tone="muted">{{ $asset->use }}</x-site.text>
                    <a href="{{ asset($asset->path) }}" download class="w-fit text-small text-accent underline underline-offset-2">Download</a>
                </figcaption>
            </figure>
        @endforeach
    </div>
</div>
