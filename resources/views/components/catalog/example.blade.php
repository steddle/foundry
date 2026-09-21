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
            'overflow-hidden rounded-md border border-subtle',
            'p-8' => $ground !== 'bare',
            'bg-inverse ink' => $ground === 'ink',
            'bg-page' => $ground === 'page',
        ])>
            @isset($example['zoom'])
                <div style="zoom: {{ $example['zoom'] }}">{!! Blade::render($example['blade']) !!}</div>
            @else
                {!! Blade::render($example['blade']) !!}
            @endisset
        </div>
    @endunless

    <pre class="overflow-x-auto rounded-md border border-subtle bg-sunken p-4 font-mono text-code text-strong slashed-zero tabular-nums"><code>{{ $example['blade'] }}</code></pre>
</figure>
