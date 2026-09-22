@props(['label', 'action', 'size' => null])

{{--
    A button for a step that can't be undone, taken in three presses rather
    than a dialog: the words go from the label to 'Click again' ('Tap again'
    on touch) to 'One more time', and the danger ramp fills the button a third at
    a time, left to right, inverting the words it covers. The third press runs
    the action. Pressing nothing for a second and a half, or leaving the
    button, starts over.

    @group Elements
    @prop label What the button does, e.g. 'Delete account'; also its accessible name.
    @prop action The Alpine expression the third press runs, e.g. `$wire.deleteAccount()` or `$el.closest('form').requestSubmit()`.
    @prop size Flux's button size: sm or xs; without one, the default.

    @example Deleting an account
    <x-site.confirm-button label="Delete account" action="$el.dataset.done = 'yes'" />

    @example Small, in a row
    <x-site.confirm-button label="Revoke" size="sm" action="$el.dataset.done = 'yes'" />
--}}
<flux:button type="button" variant="outline" :size="$size" aria-label="{{ $label }}"
    x-data="{ step: 0, timer: null }"
    x-on:click="clearTimeout(timer); if (step === 2) { step = 3; {{ $action }}; timer = setTimeout(() => step = 0, 1500) } else { step++; timer = setTimeout(() => step = 0, 1500) }"
    x-on:blur="clearTimeout(timer); step = 0"
    {{ $attributes->class('relative overflow-hidden font-semibold! text-danger-700! dark:text-danger-300! border-danger-700/30! dark:border-danger-300/30!') }}>
    <x-site.confirm-button.labels :$label />
    <span aria-hidden="true" class="absolute inset-0 flex items-center justify-center bg-danger-700 dark:bg-danger-300 text-white dark:text-zinc-950 transition-[clip-path] duration-180 ease-(--ease-settle)"
        style="clip-path: inset(0 100% 0 0)" x-bind:style="`clip-path: inset(0 ${100 - (step / 3) * 100}% 0 0)`">
        <x-site.confirm-button.labels :$label />
    </span>
</flux:button>
