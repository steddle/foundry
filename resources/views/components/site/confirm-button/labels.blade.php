@props(['label', 'align' => 'center'])

{{-- The three words share one grid cell, as wide as the longest, so the control never resizes mid-press. Reads `step` from the control's Alpine data. --}}
<span @class(['grid', 'place-items-center' => $align === 'center', 'justify-items-start text-left' => $align === 'start'])>
    <span class="col-start-1 row-start-1" x-bind:class="{ invisible: step !== 0 }">{{ $label }}</span>
    <span class="invisible col-start-1 row-start-1" x-bind:class="{ invisible: step !== 1 }" aria-hidden="true">
        <span class="pointer-coarse:hidden">{{ __('foundry::nav.confirm_again') }}</span>
        <span class="hidden pointer-coarse:inline">{{ __('foundry::nav.confirm_again_touch') }}</span>
    </span>
    <span class="invisible col-start-1 row-start-1" x-bind:class="{ invisible: step < 2 }" aria-hidden="true">{{ __('foundry::nav.confirm_last') }}</span>
</span>
