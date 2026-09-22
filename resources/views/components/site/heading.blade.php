@props(['size' => '2', 'level' => null, 'tone' => 'strong'])

{{--
    Spectral, on the imprint's scale. Colour comes from `tone` alone, strong,
    accent or inherit, so no caller sets two.

    @group Type

    @example The four sizes
    <div class="flex flex-col gap-6">
        <x-site.heading size="display">Display heading.</x-site.heading>
        <x-site.heading size="1">Heading one.</x-site.heading>
        <x-site.heading size="2">Heading two.</x-site.heading>
        <x-site.heading size="3">Heading three.</x-site.heading>
    </div>
--}}
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

<flux:heading :$level {{ $attributes->class(['font-serif text-balance', $sizes[$size], $tones[$tone]]) }}>{{ $slot }}</flux:heading>
