@props(['tone' => 'neutral', 'dot' => false])

@php
    // Each status ramp's 50 as the ground and 700 as the text, in both themes. Lichen is the one tone that asks the reader to act.
    $tones = [
        'neutral' => 'bg-zinc-100! text-zinc-700! dark:bg-zinc-800! dark:text-zinc-300!',
        'info' => 'bg-info-50! text-info-700!',
        'success' => 'bg-success-50! text-success-700!',
        'warning' => 'bg-warning-50! text-warning-700!',
        'danger' => 'bg-danger-50! text-danger-700!',
        'action' => 'bg-secondary-50! text-secondary-700!',
    ];
@endphp

{{-- A short status beside what it describes, drawn by Flux in the family's colours. --}}
<flux:badge size="sm" {{ $attributes->class(['w-fit gap-1.5 rounded-sm! font-semibold', $tones[$tone]]) }}>
    @if ($dot)
        <span class="size-1.5 shrink-0 rounded-full bg-current" aria-hidden="true"></span>
    @endif
    {{ $slot }}
</flux:badge>
