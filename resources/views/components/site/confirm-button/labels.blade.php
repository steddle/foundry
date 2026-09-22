@props(['label', 'align' => 'center', 'done' => false])

{{--
    The words share one grid cell, as wide as the longest, so the control
    never resizes mid-press. Reads `step` from the control's Alpine data: 0
    the label, 1 again, 2 and 3 the last ask, 4 done where `done` is set.
--}}
<span @class(['grid', 'place-items-center' => $align === 'center', 'justify-items-start text-left' => $align === 'start'])>
    <span class="col-start-1 row-start-1" x-bind:class="{ invisible: step !== 0 }">{{ $label }}</span>
    <span class="invisible col-start-1 row-start-1" x-bind:class="{ invisible: step !== 1 }" aria-hidden="true">
        <span class="pointer-coarse:hidden">{{ __('foundry::nav.confirm_again') }}</span>
        <span class="hidden pointer-coarse:inline">{{ __('foundry::nav.confirm_again_touch') }}</span>
    </span>
    <span class="invisible col-start-1 row-start-1" x-bind:class="{ invisible: step !== 2 && step !== 3 }" aria-hidden="true">{{ __('foundry::nav.confirm_last') }}</span>
    @if ($done)
        <span class="invisible col-start-1 row-start-1" x-bind:class="{ invisible: step !== 4 }" aria-hidden="true">✓ {{ __('foundry::nav.confirm_done') }}</span>
    @endif
</span>
