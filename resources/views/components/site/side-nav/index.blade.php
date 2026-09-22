@props(['groups', 'label' => 'Contents'])

{{--
    $groups: title => list of ['label', 'href', 'current' => bool]. Closed behind a disclosure below lg.

    @group Navigation

    @example Two groups
    <x-site.side-nav :groups="[
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
        <x-site.side-nav.list :groups="$groups" />
    </div>
</details>
<x-site.side-nav.list :groups="$groups" class="max-lg:hidden" />
