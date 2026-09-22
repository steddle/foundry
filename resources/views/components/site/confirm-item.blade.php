@props(['action', 'icon' => null])

{{--
    x-site.confirm-button as a row in a menu: three presses, the words going
    from the label to 'Click again' to 'One more time' while a danger wash
    grows behind the icon and the words a third at a time. Pressing nothing
    for a second and a half after a pointer press, moving the pointer off the
    row or moving focus off it starts over; a press by keyboard starts no
    timer. A status region says each step to a screen reader.
    The third press fills the row, and once the fill has landed the menu
    closes and the action runs. The action is an Alpine expression rather
    than a wire:click on the item, which would fire on the first press. A
    Flux item closes its menu by dispatching `lofi-close-popovers` on
    mouseup, before the click, so the wrapper stops every such event from
    the item and closes the menu itself, on the menu.

    @group Navigation
    @prop action The Alpine expression the third press runs, e.g. `$wire.delete('…')`.
    @prop icon A Flux icon before the words.
    @slot slot What the item does, e.g. 'Delete'.

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
<div class="contents" x-data="{ step: 0, timer: null }"
    x-on:click.capture="$event.preventDefault(); $event.stopPropagation(); clearTimeout(timer); if (step >= 2) { step = 3; timer = setTimeout(() => { $el.closest('ui-menu')?.dispatchEvent(new CustomEvent('lofi-close-popovers')); {{ $action }}; timer = setTimeout(() => step = 0, 300) }, 240) } else { step++; if ($event.detail > 0) timer = setTimeout(() => step = 0, 1500) }"
    x-on:lofi-close-popovers="$event.stopPropagation()"
    x-on:mouseleave="if (step < 3) { clearTimeout(timer); step = 0 }"
    x-on:focusout="if (step < 3) { clearTimeout(timer); step = 0 }">
    <flux:menu.item variant="danger" :$icon x-bind:data-step="step" {{ $attributes->class('relative isolate overflow-hidden text-danger-700! dark:text-danger-300! **:data-flux-menu-item-icon:text-current!') }}>
        <span aria-hidden="true" class="absolute inset-y-0 left-0 -z-10 bg-danger-700/15 dark:bg-danger-300/20 transition-[width] duration-180 ease-(--ease-settle)"
            style="width: 0%" x-bind:style="`width: ${(step / 3) * 100}%`"></span>
        <x-site.confirm-button.labels :label="$slot" align="start" />
    </flux:menu.item>
    <span role="status" class="sr-only" x-text="{ 1: @js(__('foundry::nav.confirm_again_status')), 2: @js(__('foundry::nav.confirm_last')) }[step] ?? ''"></span>
</div>
