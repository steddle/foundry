@props(['action', 'icon' => null, 'confirm' => null])

{{--
    A menu item for a step that can't be undone, taken in three clicks: the
    menu opens, the item arms, and only the click on the armed item acts.
    Armed, it reads 'Click again to confirm' in the danger ramp and the menu
    stays open; it disarms after four seconds or when the pointer leaves it.
    The action is an Alpine expression rather than a wire:click on the item,
    which would fire on the arming click too. A Flux item closes its menu by
    dispatching `lofi-close-popovers` on mouseup, before the click, so the
    wrapper stops that event until the item is armed and lets the confirming
    press close the menu.

    @group Navigation
    @prop action The Alpine expression the confirming click runs, e.g. `$wire.delete('…')` or `$el.closest('form').requestSubmit()`.
    @prop icon A Flux icon before the words.
    @prop confirm The armed words; without them, `foundry::nav.confirm`.

    @example In a row's menu
    <flux:dropdown>
        <flux:button size="sm" variant="ghost" icon="ellipsis-horizontal" aria-label="Actions" />
        <flux:menu>
            <flux:menu.item icon="arrow-top-right-on-square">Open</flux:menu.item>
            <flux:menu.separator />
            <x-site.confirm-item icon="trash" action="$el.dataset.done = 'yes'">Delete</x-site.confirm-item>
        </flux:menu>
    </flux:dropdown>
--}}
<div class="contents" x-data="{ armed: false, timer: null }"
    x-on:click.capture="if (! armed) { $event.preventDefault(); $event.stopPropagation(); armed = true; clearTimeout(timer); timer = setTimeout(() => armed = false, 4000) } else { armed = false; clearTimeout(timer); {{ $action }} }"
    x-on:lofi-close-popovers="armed || $event.stopPropagation()"
    x-on:mouseleave="armed = false; clearTimeout(timer)">
    <flux:menu.item variant="danger" :$icon x-bind:data-armed="armed" x-bind:class="armed && 'bg-danger-50! text-danger-700! dark:bg-danger-300/15! dark:text-danger-300!'" {{ $attributes }}>
        <span x-show="! armed">{{ $slot }}</span>
        <span x-show="armed" x-cloak>{{ $confirm ?? __('foundry::nav.confirm') }}</span>
    </flux:menu.item>
</div>
