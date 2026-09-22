@props(['groups', 'label' => 'Contents'])

{{--
    Links to a document's pages, in titled groups. Closed behind a
    disclosure below lg, open beside the text from lg up.

    @group Navigation
    @prop groups title => list of ['label' => …, 'href' => …, 'current' => bool]; the current link is marked in the accent and as aria-current="page".
    @prop label What the disclosure reads below lg.

    @example Two groups
    <foundry:side-nav :groups="[
        'Getting started' => [['label' => 'Installation', 'href' => '#', 'current' => true], ['label' => 'Theming', 'href' => '#']],
        'Components' => [['label' => 'Button', 'href' => '#'], ['label' => 'Heading', 'href' => '#']],
    ]" />
--}}
<details {{ $attributes->class('group rounded-md border border-zinc-200 dark:border-zinc-700 lg:hidden') }}>
    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-4 py-3 font-medium text-zinc-950 dark:text-zinc-50">
        {{ $label }}
        <flux:icon.chevron-down variant="micro" class="fill-zinc-600 dark:fill-zinc-400 group-open:rotate-180" />
    </summary>
    <div class="border-t border-zinc-200 dark:border-zinc-700 p-4">
        <foundry:side-nav.list :groups="$groups" />
    </div>
</details>
<foundry:side-nav.list :groups="$groups" class="max-lg:hidden" />
