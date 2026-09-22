{{--
    What something may do or includes: a list of foundry:checklist.item, each ticked in the accent.

    @group Elements

    @example What a client may do
    <foundry:checklist>
        <foundry:checklist.item>Search the corpus and read what it holds</foundry:checklist.item>
        <foundry:checklist.item>Nothing it writes</foundry:checklist.item>
    </foundry:checklist>
--}}
<ul role="list" {{ $attributes->class('flex flex-col gap-2 text-copy text-zinc-800 dark:text-zinc-200') }}>
    {{ $slot }}
</ul>
