@use('Steddle\Foundry\Brand\BrandAssets')

@props(['group' => 'social'])

{{--
    What foundry:assets renders, as /design shows it. No card ground: a
    section on ink keeps its own.

    @group Brand
    @prop group social or icon: the social images in pairs of one height, each with a download link, or the icons at a glance with the files written beside them.

    @example Icons
    <foundry:brand-assets group="icon" />
--}}
@php
    $manifest = BrandAssets::manifest();
    $assets = collect(BrandAssets::all())->where('group', $group);
    $version = fn (string $name): string => substr($manifest[$name] ?? '', 0, 8);
@endphp

<div {{ $attributes->class('flex flex-col gap-8') }}>
    <foundry:text tone="muted" class="max-w-[64ch] hyphens-manual">
        @if ($group === 'social')
            Rendered from config/imprint.php by <code class="font-mono text-code text-zinc-950 dark:text-zinc-50 slashed-zero tabular-nums">php artisan foundry:assets</code>. og-image.png stands in where OG Kit has no key; with one, ogkit.dev renders each page's own template.
        @else
            Drawn from foundry:mark in the imprint's ink and paper by <code class="font-mono text-code text-zinc-950 dark:text-zinc-50 slashed-zero tabular-nums">php artisan foundry:assets</code>, with favicon.ico cut from icon-512.png and site.webmanifest written beside them.
        @endif
    </foundry:text>

    @if ($group === 'social')
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            @foreach ($assets as $asset)
                <figure class="flex flex-col gap-3 rounded-md border border-zinc-200 dark:border-zinc-700 p-4">
                    <img src="{{ asset($asset->path) }}?v={{ $version($asset->name) }}" alt="{{ basename($asset->path) }}" width="{{ $asset->width }}" height="{{ $asset->height }}" class="h-auto w-full rounded-sm border border-zinc-200 dark:border-zinc-700" loading="lazy">
                    <figcaption class="flex flex-col gap-1">
                        <foundry:text variant="small" tone="strong" class="font-medium tabular-nums">{{ basename($asset->path) }} | {{ $asset->width }}×{{ $asset->height }}</foundry:text>
                        <foundry:text variant="small" tone="muted">{{ $asset->use }}</foundry:text>
                        <a href="{{ asset($asset->path) }}" download class="w-fit text-small text-primary-700 dark:text-primary-300 underline underline-offset-2">Download</a>
                    </figcaption>
                </figure>
            @endforeach
        </div>
    @else
        <div class="grid grid-cols-2 gap-6 sm:grid-cols-3 lg:grid-cols-4">
            @foreach ($assets as $asset)
                <figure class="flex flex-col gap-3 rounded-md border border-zinc-200 dark:border-zinc-700 p-4">
                    <img src="{{ asset($asset->path) }}?v={{ $version($asset->name) }}" alt="{{ basename($asset->path) }}" width="64" height="64" class="size-16" loading="lazy">
                    <figcaption class="flex flex-col gap-1">
                        <foundry:text variant="small" tone="strong" class="font-medium tabular-nums"><span class="break-all">{{ basename($asset->path) }}</span> <span class="whitespace-nowrap">| {{ $asset->width }}×{{ $asset->height }}</span></foundry:text>
                        <foundry:text variant="small" tone="muted">{{ $asset->use }}</foundry:text>
                    </figcaption>
                </figure>
            @endforeach
        </div>

        <div class="flex flex-wrap items-center gap-6">
            @foreach (['favicon.ico', ...array_keys(BrandAssets::files())] as $path)
                <a href="{{ asset($path) }}" download class="flex items-center gap-3 text-small text-primary-700 dark:text-primary-300 underline underline-offset-2">
                    @if (! str_ends_with($path, '.webmanifest'))
                        <img src="{{ asset($path) }}" alt="" width="32" height="32" class="size-8">
                    @endif
                    {{ basename($path) }}
                </a>
            @endforeach
        </div>
    @endif
</div>
