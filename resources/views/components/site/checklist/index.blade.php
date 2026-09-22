{{--
    What something may do or includes: a list of x-site.checklist.item, each ticked in the accent.

    @group Elements

    @example What a client may do
    <x-site.checklist>
        <x-site.checklist.item>Search the corpus and read what it holds</x-site.checklist.item>
        <x-site.checklist.item>Nothing it writes</x-site.checklist.item>
    </x-site.checklist>
--}}
<ul role="list" {{ $attributes->class('flex flex-col gap-2 text-copy text-zinc-800 dark:text-zinc-200') }}>
    {{ $slot }}
</ul>
