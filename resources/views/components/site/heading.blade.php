@props(['size' => '2', 'level' => null, 'tone' => 'strong'])

@php
    // Flux sets a plain text-sm and font-medium on every heading, so the scale overrides both.
    $sizes = [
        'display' => 'text-display! font-semibold!',
        '1' => 'text-heading-1! font-semibold!',
        '2' => 'text-heading-2! font-semibold!',
        '3' => 'text-heading-3! font-semibold!',
    ];

    $tones = ['strong' => 'text-zinc-950 dark:text-zinc-50', 'accent' => 'text-primary-700 dark:text-primary-300', 'inherit' => ''];
@endphp

{{-- A heading in Spectral. Colour comes from `tone` alone, so no caller sets two. --}}
<flux:heading :$level {{ $attributes->class(['font-serif text-balance', $sizes[$size], $tones[$tone]]) }}>{{ $slot }}</flux:heading>
