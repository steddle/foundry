@use('Illuminate\Support\Facades\Blade')

@props(['example', 'slug', 'index'])

@php
    $ground = $example['ground'] ?? 'page';
    $grounds = ['page' => 'bg-zinc-50 dark:bg-zinc-900', 'ink' => 'bg-zinc-900 ink'];
    $choice = 'rounded-sm! px-2.5! text-small! text-zinc-600! dark:text-zinc-400! hover:text-zinc-950! dark:hover:text-zinc-50! aria-pressed:bg-zinc-200! aria-pressed:text-zinc-950! dark:aria-pressed:bg-zinc-700! dark:aria-pressed:text-zinc-50!';
@endphp

{{--
    `bare` renders on a page of its own in a frame at a desktop's or a phone's
    width, scaled to the column, so it meets its breakpoints as a page does.
--}}
<figure class="flex flex-col gap-3" data-example>
    <div class="flex flex-wrap items-center justify-between gap-3">
        <foundry:text variant="label" tone="muted">{{ $example['title'] }}</foundry:text>
        @unless ($example['code'] ?? false)
            <div class="flex rounded-md border border-zinc-200 dark:border-zinc-700 p-0.5 text-small font-medium" role="group" aria-label="{{ $ground === 'bare' ? 'Width' : 'Ground' }}">
                @if ($ground === 'bare')
                    <flux:button variant="subtle" size="xs" data-width="1280" aria-pressed="true" :class="$choice">Desktop</flux:button>
                    <flux:button variant="subtle" size="xs" data-width="390" aria-pressed="false" :class="$choice">Phone</flux:button>
                @else
                    @foreach ($grounds as $name => $classes)
                        <flux:button variant="subtle" size="xs" :data-ground="$classes" :aria-pressed="$name === $ground ? 'true' : 'false'" :class="$choice">{{ ucfirst($name) }}</flux:button>
                    @endforeach
                @endif
            </div>
        @endunless
    </div>

    @unless ($example['code'] ?? false)
        @if ($ground === 'bare')
            <div class="overflow-hidden rounded-md border border-zinc-200 dark:border-zinc-700">
                <iframe src="{{ route('foundry.components.example', [$slug, $index]) }}" title="{{ $example['title'] }}" data-width="1280" loading="lazy" class="block h-96 origin-top-left border-0"></iframe>
            </div>
        @else
            <div data-canvas class="overflow-hidden rounded-md border border-zinc-200 dark:border-zinc-700 p-8 {{ $grounds[$ground] }}">
                {!! Blade::render($example['blade']) !!}
            </div>
        @endif
    @endunless

    <div class="relative">
        <pre class="overflow-x-auto rounded-md border border-zinc-200 dark:border-zinc-700 bg-zinc-100 dark:bg-zinc-950 p-4 pr-20 font-mono text-code text-zinc-950 dark:text-zinc-50 slashed-zero tabular-nums"><code>{{ $example['blade'] }}</code></pre>
        <div class="absolute top-2 right-2">
            <foundry:button variant="secondary" size="xs" data-copy-example>Copy</foundry:button>
        </div>
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
            {{-- Only the example's own controls: an example's content has buttons of its own. --}}
            const button = event.target.closest('[data-example] button:is([data-copy-example], [data-width], [data-ground])');

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
        let refit = 0;
        addEventListener('resize', () => {
            cancelAnimationFrame(refit);
            refit = requestAnimationFrame(() => document.querySelectorAll('[data-example] iframe').forEach(fitExample));
        });
    </script>
@endonce
