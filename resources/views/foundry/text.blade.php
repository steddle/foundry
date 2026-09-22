@props(['variant' => 'copy', 'tone' => 'body', 'inline' => false])

{{--
    Chivo, in a variant for its role and a tone for its colour, drawn by
    Flux.

    @group Type
    @prop variant lede, copy, small, label or meta: the step of the type scale; meta sets tabular numerals.
    @prop tone body, strong, muted, accent, error or inherit: the colour, or none of its own.
    @prop inline Renders a span instead of a paragraph.

    @example Variants
    <div class="flex flex-col gap-4">
        <foundry:text variant="lede">A lede opens a section.</foundry:text>
        <foundry:text>Copy carries the text.</foundry:text>
        <foundry:text variant="small">Small sets a caption or a note.</foundry:text>
        <foundry:text variant="label" tone="muted">A label names what follows</foundry:text>
    </div>
--}}
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
