@props(['tone' => 'neutral', 'dot' => false])

{{--
    A short status beside what it describes, drawn by Flux: on bone a status
    ramp's 50 as the ground and its 700 as the text, and on ink or in the
    dark its 300 as the text over a wash of it, as the foundry sets colour on
    ink. Neutral is the grey, 100 under 700 on bone and 800 under 300 on ink.
    A hairline ring inside the edge, in the text's colour at 10% on bone and
    15% on ink, holds the badge off a ground of the same wash.

    @group Elements
    @prop tone neutral, info, success, warning, danger or action: the ramp the badge is drawn in. Action, in lichen, is the one tone that asks the reader to do something.
    @prop dot Sets a dot in the text's colour before the words.

    @example Every tone
    <div class="flex flex-wrap items-center gap-2.5">
        <x-site.badge>Neutral</x-site.badge>
        <x-site.badge tone="info">Info</x-site.badge>
        <x-site.badge tone="success">Success</x-site.badge>
        <x-site.badge tone="warning">Warning</x-site.badge>
        <x-site.badge tone="danger">Danger</x-site.badge>
        <x-site.badge tone="action" dot>Action</x-site.badge>
    </div>
--}}
@php
    $tones = [
        'neutral' => 'bg-zinc-100! text-zinc-700! ring-zinc-700/10 dark:bg-zinc-800! dark:text-zinc-300! dark:ring-zinc-300/15',
        'info' => 'bg-info-50! text-info-700! ring-info-700/10 dark:bg-info-300/15! dark:text-info-300! dark:ring-info-300/15',
        'success' => 'bg-success-50! text-success-700! ring-success-700/10 dark:bg-success-300/15! dark:text-success-300! dark:ring-success-300/15',
        'warning' => 'bg-warning-50! text-warning-700! ring-warning-700/10 dark:bg-warning-300/15! dark:text-warning-300! dark:ring-warning-300/15',
        'danger' => 'bg-danger-50! text-danger-700! ring-danger-700/10 dark:bg-danger-300/15! dark:text-danger-300! dark:ring-danger-300/15',
        'action' => 'bg-secondary-50! text-secondary-700! ring-secondary-700/10 dark:bg-secondary-300/15! dark:text-secondary-300! dark:ring-secondary-300/15',
    ];
@endphp

<flux:badge size="sm" {{ $attributes->class(['w-fit gap-1.5 rounded-sm! font-semibold ring-1 ring-inset', $tones[$tone]]) }}>
    @if ($dot)
        <span class="size-1.5 shrink-0 rounded-full bg-current" aria-hidden="true"></span>
    @endif
    {{ $slot }}
</flux:badge>
