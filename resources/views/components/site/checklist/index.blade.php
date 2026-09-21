{{-- What something may do or includes: a list of x-site.checklist.item, each ticked in the accent. --}}
<ul role="list" {{ $attributes->class('flex flex-col gap-2 text-copy text-zinc-800 dark:text-zinc-200') }}>
    {{ $slot }}
</ul>
