@props(['label', 'action', 'size' => null])

{{--
    A button for a step that can't be undone, taken in three presses rather
    than a dialog: the words go from the label to 'Click again' ('Tap again'
    on touch) to 'One more time', and the danger ramp fills the button a
    third at a time, left to right, inverting the words it covers. The third
    press fills it, and the action runs once the fill has landed. When the
    action has finished (a `$wire` call's promise included) the filled button
    reads '✓ Done' for a second, then drains back to its label, slower than
    it filled; an action that fails drains it without the Done. Pressing
    nothing for a second and a half, moving the pointer off the button or
    leaving it by keyboard starts over, until the third press.

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
    x-data="{ step: 0, timer: null, draining: false, drain() { this.draining = true; this.step = 0; this.timer = setTimeout(() => this.draining = false, 500) } }"
    x-on:click="if (step >= 3) return; clearTimeout(timer); if (step === 2) { step = 3; timer = setTimeout(() => Promise.resolve().then(() => {{ $action }}).then(() => { step = 4; timer = setTimeout(() => drain(), 1200) }, () => drain()), 220) } else { step++; timer = setTimeout(() => step = 0, 1500) }"
    x-on:blur="if (step < 3) { clearTimeout(timer); step = 0 }"
    x-on:mouseleave="if (step < 3) { clearTimeout(timer); step = 0 }"
    {{ $attributes->class('relative overflow-hidden font-semibold! text-danger-700! dark:text-danger-300! border-danger-700/30! dark:border-danger-300/30!') }}>
    <x-site.confirm-button.labels :$label done />
    <span aria-hidden="true" class="absolute inset-0 flex items-center justify-center bg-danger-700 dark:bg-danger-300 text-white dark:text-zinc-950 transition-[clip-path] ease-(--ease-settle)"
        x-bind:class="draining ? 'duration-500' : 'duration-180'"
        style="clip-path: inset(0 100% 0 0)" x-bind:style="`clip-path: inset(0 ${100 - (Math.min(step, 3) / 3) * 100}% 0 0)`">
        <x-site.confirm-button.labels :$label done />
    </span>
</flux:button>
