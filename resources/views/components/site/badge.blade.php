@props(['tone' => 'neutral', 'dot' => false])

{{--
    A short status beside what it describes, drawn by Flux: a status ramp's
    50 as the ground and its 700 as the text, in both themes, and neutral in
    the grey, 100 under 700 on bone and 800 under 300 in the dark.

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
        'neutral' => 'bg-zinc-100! text-zinc-700! dark:bg-zinc-800! dark:text-zinc-300!',
        'info' => 'bg-info-50! text-info-700!',
        'success' => 'bg-success-50! text-success-700!',
        'warning' => 'bg-warning-50! text-warning-700!',
        'danger' => 'bg-danger-50! text-danger-700!',
        'action' => 'bg-secondary-50! text-secondary-700!',
    ];
@endphp

<flux:badge size="sm" {{ $attributes->class(['w-fit gap-1.5 rounded-sm! font-semibold', $tones[$tone]]) }}>
    @if ($dot)
        <span class="size-1.5 shrink-0 rounded-full bg-current" aria-hidden="true"></span>
    @endif
    {{ $slot }}
</flux:badge>
