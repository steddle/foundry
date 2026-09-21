@props(['groups', 'label' => 'Contents'])

{{-- $groups: title => list of ['label', 'href', 'current' => bool]. Closed behind a disclosure below lg. --}}
<details {{ $attributes->class('group rounded-md border border-subtle lg:hidden') }}>
    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-4 py-3 font-medium text-strong">
        {{ $label }}
        <flux:icon.chevron-down variant="micro" class="fill-muted group-open:rotate-180" />
    </summary>
    <div class="border-t border-subtle p-4">
        <x-site.side-nav.list :groups="$groups" />
    </div>
</details>
<x-site.side-nav.list :groups="$groups" class="max-lg:hidden" />
