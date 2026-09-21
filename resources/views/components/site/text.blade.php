@props(['variant' => 'copy', 'tone' => 'body', 'inline' => false])

@php
    $variants = [
        'lede' => 'text-lede text-pretty',
        'copy' => 'text-copy text-pretty',
        'small' => 'text-small',
        'label' => 'text-label',
        'meta' => 'text-meta tabular-nums',
    ];

    $tones = [
        'body' => 'text-zinc-800 dark:text-zinc-200',
        'strong' => 'text-zinc-950 dark:text-zinc-50',
        'muted' => 'text-zinc-600 dark:text-zinc-400',
        'accent' => 'text-primary-700 dark:text-primary-300',
        'error' => 'text-danger-700 dark:text-danger-400',
        'inherit' => '',
    ];
@endphp

{{-- Flux sets its size and colour inside :where(), so these classes win without `!`. Colour comes from `tone` alone. --}}
<flux:text :$inline {{ $attributes->class([$variants[$variant], $tones[$tone]]) }}>{{ $slot }}</flux:text>
