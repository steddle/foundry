@use('Steddle\Foundry\Brand\BrandAssets')

@props(['group' => 'social'])

@php
    $manifest = BrandAssets::manifest();
    $assets = collect(BrandAssets::all())->where('group', $group);
    $version = fn (string $name): string => substr($manifest[$name] ?? '', 0, 8);
@endphp

{{-- What foundry:assets makes, as each imprint's /design shows it. Social images in pairs of one height; icons at a glance, with the files written beside them. No card ground: a section on ink keeps its own. --}}
<div {{ $attributes->class('flex flex-col gap-8') }}>
    <x-site.text tone="muted" class="max-w-[64ch] hyphens-manual">
        @if ($group === 'social')
            Rendered from config/imprint.php by <code class="font-mono text-code text-strong slashed-zero tabular-nums">php artisan foundry:assets</code>. og-image.png stands in where OG Kit has no key; with one, ogkit.dev renders each page's own template.
        @else
            Drawn from x-site.mark in the imprint's ink and paper by <code class="font-mono text-code text-strong slashed-zero tabular-nums">php artisan foundry:assets</code>, with favicon.ico cut from icon-512.png and site.webmanifest written beside them.
        @endif
    </x-site.text>

    @if ($group === 'social')
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            @foreach ($assets as $asset)
                <figure class="flex flex-col gap-3 rounded-md border border-subtle p-4">
                    <img src="{{ asset($asset->path) }}?v={{ $version($asset->name) }}" alt="{{ basename($asset->path) }}" width="{{ $asset->width }}" height="{{ $asset->height }}" class="h-auto w-full rounded-sm border border-subtle" loading="lazy">
                    <figcaption class="flex flex-col gap-1">
                        <x-site.text variant="small" tone="strong" class="font-medium tabular-nums">{{ basename($asset->path) }} | {{ $asset->width }}×{{ $asset->height }}</x-site.text>
                        <x-site.text variant="small" tone="muted">{{ $asset->use }}</x-site.text>
                        <a href="{{ asset($asset->path) }}" download class="w-fit text-small text-accent underline underline-offset-2">Download</a>
                    </figcaption>
                </figure>
            @endforeach
        </div>
    @else
        <div class="grid grid-cols-2 gap-6 sm:grid-cols-3 lg:grid-cols-4">
            @foreach ($assets as $asset)
                <figure class="flex flex-col gap-3 rounded-md border border-subtle p-4">
                    <img src="{{ asset($asset->path) }}?v={{ $version($asset->name) }}" alt="{{ basename($asset->path) }}" width="64" height="64" class="size-16" loading="lazy">
                    <figcaption class="flex flex-col gap-1">
                        <x-site.text variant="small" tone="strong" class="font-medium tabular-nums"><span class="break-all">{{ basename($asset->path) }}</span> <span class="whitespace-nowrap">| {{ $asset->width }}×{{ $asset->height }}</span></x-site.text>
                        <x-site.text variant="small" tone="muted">{{ $asset->use }}</x-site.text>
                    </figcaption>
                </figure>
            @endforeach
        </div>

        <div class="flex flex-wrap items-center gap-6">
            @foreach (['favicon.ico', ...array_keys(BrandAssets::files())] as $path)
                <a href="{{ asset($path) }}" download class="flex items-center gap-3 text-small text-accent underline underline-offset-2">
                    @if (! str_ends_with($path, '.webmanifest'))
                        <img src="{{ asset($path) }}" alt="" width="32" height="32" class="size-8">
                    @endif
                    {{ basename($path) }}
                </a>
            @endforeach
        </div>
    @endif
</div>
