@use('Illuminate\Support\Facades\Blade')

@props(['example'])

@php
    $ground = $example['ground'] ?? 'page';
@endphp

{{-- One example: the Blade rendered live on its ground, then the Blade itself. `bare` gives the render no padding, for a band or an image that brings its own. --}}
<figure class="flex flex-col gap-3">
    <x-site.text variant="label" tone="muted">{{ $example['title'] }}</x-site.text>

    @unless ($example['code'] ?? false)
        <div @class([
            'overflow-hidden rounded-md border border-zinc-200 dark:border-zinc-700',
            'p-8' => $ground !== 'bare',
            'bg-zinc-900 ink' => $ground === 'ink',
            'bg-zinc-50 dark:bg-zinc-900' => $ground === 'page',
        ])>
            @isset($example['zoom'])
                <div style="zoom: {{ $example['zoom'] }}">{!! Blade::render($example['blade']) !!}</div>
            @else
                {!! Blade::render($example['blade']) !!}
            @endisset
        </div>
    @endunless

    <pre class="overflow-x-auto rounded-md border border-zinc-200 dark:border-zinc-700 bg-zinc-100 dark:bg-zinc-950 p-4 font-mono text-code text-zinc-950 dark:text-zinc-50 slashed-zero tabular-nums"><code>{{ $example['blade'] }}</code></pre>
</figure>
