@use('Illuminate\Support\Facades\Blade')

@props(['example', 'slug', 'index'])

@php
    $ground = $example['ground'] ?? 'page';
    $grounds = ['page' => 'bg-zinc-50 dark:bg-zinc-900', 'ink' => 'bg-zinc-900 ink'];
    $choice = 'rounded-sm px-2.5 py-0.5 text-zinc-600 dark:text-zinc-400 hover:text-zinc-950 dark:hover:text-zinc-50 aria-pressed:bg-zinc-200 aria-pressed:text-zinc-950 dark:aria-pressed:bg-zinc-700 dark:aria-pressed:text-zinc-50';
@endphp

{{--
    One example: the Blade rendered live, then the Blade itself with a copy
    button. `bare` is a band or a page that brings its own ground: it renders
    on a page of its own in a frame at a desktop's or a phone's width, scaled
    to the column, so it meets its breakpoints as a page does. Anything else
    renders in place, on the page's ground or on ink.
--}}
<figure class="flex flex-col gap-3" data-example>
    <div class="flex flex-wrap items-center justify-between gap-3">
        <x-site.text variant="label" tone="muted">{{ $example['title'] }}</x-site.text>
        @unless ($example['code'] ?? false)
            <div class="flex rounded-md border border-zinc-200 dark:border-zinc-700 p-0.5 text-small font-medium" role="group" aria-label="{{ $ground === 'bare' ? 'Width' : 'Ground' }}">
                @if ($ground === 'bare')
                    <button type="button" data-width="1280" aria-pressed="true" class="{{ $choice }}">Desktop</button>
                    <button type="button" data-width="390" aria-pressed="false" class="{{ $choice }}">Phone</button>
                @else
                    @foreach ($grounds as $name => $classes)
                        <button type="button" data-ground="{{ $classes }}" aria-pressed="{{ $name === $ground ? 'true' : 'false' }}" class="{{ $choice }}">{{ ucfirst($name) }}</button>
                    @endforeach
                @endif
            </div>
        @endunless
    </div>

    @unless ($example['code'] ?? false)
        @if ($ground === 'bare')
            <div class="overflow-hidden rounded-md border border-zinc-200 dark:border-zinc-700">
                <iframe src="{{ route('foundry.components.example', [$slug, $index]) }}" title="{{ $example['title'] }}" data-width="1280" class="block h-96 origin-top-left border-0"></iframe>
            </div>
        @else
            <div data-canvas class="overflow-hidden rounded-md border border-zinc-200 dark:border-zinc-700 p-8 {{ $grounds[$ground] }}">
                @isset($example['zoom'])
                    <div style="zoom: {{ $example['zoom'] }}">{!! Blade::render($example['blade']) !!}</div>
                @else
                    {!! Blade::render($example['blade']) !!}
                @endisset
            </div>
        @endif
    @endunless

    <div class="relative">
        <pre class="overflow-x-auto rounded-md border border-zinc-200 dark:border-zinc-700 bg-zinc-100 dark:bg-zinc-950 p-4 pr-20 font-mono text-code text-zinc-950 dark:text-zinc-50 slashed-zero tabular-nums"><code>{{ $example['blade'] }}</code></pre>
        <button type="button" data-copy-example class="absolute top-2 right-2 rounded-sm border border-zinc-200 dark:border-zinc-700 bg-zinc-25 dark:bg-zinc-800 px-2 py-0.5 text-small font-medium text-zinc-600 dark:text-zinc-400 hover:text-zinc-950 dark:hover:text-zinc-50">Copy</button>
    </div>
</figure>

@once
    <script>
        {{-- A frame is as tall as its page at the width it is set to, scaled to the column. The page is the same origin, so its height can be read. --}}
        const fitExample = (frame) => {
            const width = Number(frame.dataset.width);
            const scale = Math.min(1, frame.parentElement.clientWidth / width);
            frame.style.width = `${width}px`;
            frame.style.transform = `scale(${scale})`;
            requestAnimationFrame(() => {
                const height = frame.contentDocument?.documentElement.scrollHeight ?? 384;
                frame.style.height = `${height}px`;
                frame.parentElement.style.height = `${height * scale}px`;
            });
        };

        document.addEventListener('click', (event) => {
            const button = event.target.closest('[data-example] button');

            if (! button) {
                return;
            }

            const example = button.closest('[data-example]');

            if (button.hasAttribute('data-copy-example')) {
                navigator.clipboard.writeText(example.querySelector('code').textContent);
                button.textContent = 'Copied';
                setTimeout(() => (button.textContent = 'Copy'), 1500);

                return;
            }

            button.parentElement.querySelectorAll('button').forEach((choice) => choice.setAttribute('aria-pressed', String(choice === button)));

            if (button.dataset.width) {
                const frame = example.querySelector('iframe');
                frame.dataset.width = button.dataset.width;
                fitExample(frame);
            } else {
                const canvas = example.querySelector('[data-canvas]');
                button.parentElement.querySelectorAll('button').forEach((choice) => canvas.classList.remove(...choice.dataset.ground.split(' ')));
                canvas.classList.add(...button.dataset.ground.split(' '));
            }
        });

        {{-- A load does not bubble, so the listener captures it: the frames below this script have not been parsed yet. --}}
        document.addEventListener('load', (event) => event.target.matches?.('[data-example] iframe') && fitExample(event.target), true);
        addEventListener('resize', () => document.querySelectorAll('[data-example] iframe').forEach(fitExample));
    </script>
@endonce
