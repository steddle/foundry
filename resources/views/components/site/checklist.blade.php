@props(['items'])

{{-- What something may do or includes, each line ticked in the accent. `$items`: list of lines. --}}
<ul role="list" {{ $attributes->class('flex flex-col gap-2 text-copy text-zinc-800 dark:text-zinc-200') }}>
    @foreach ($items as $item)
        <li class="flex items-baseline gap-2"><span class="text-primary-700 dark:text-primary-300" aria-hidden="true">✓</span>{{ $item }}</li>
    @endforeach
</ul>
