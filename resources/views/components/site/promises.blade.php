@props(['items'])

{{-- Numbered commitments, two to a row on a wide screen. `$items`: list of [title, body]. --}}
<dl {{ $attributes->class('grid grid-cols-1 gap-x-12 border-t border-zinc-200 dark:border-zinc-700 lg:grid-cols-2') }}>
    @foreach ($items as $i => [$title, $body])
        <div class="flex gap-6 border-b border-zinc-200 dark:border-zinc-700 py-6">
            <p class="w-6 shrink-0 pt-1 text-small font-medium text-zinc-600 dark:text-zinc-400 tabular-nums">{{ sprintf('%02d', $i + 1) }}</p>
            <div class="flex flex-col gap-1.5">
                <dt><x-site.heading size="2">{{ $title }}</x-site.heading></dt>
                <dd class="max-w-[52ch] text-copy text-pretty">{{ $body }}</dd>
            </div>
        </div>
    @endforeach
</dl>
