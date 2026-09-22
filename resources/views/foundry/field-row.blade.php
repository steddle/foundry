@props(['label', 'description' => null, 'for' => null])

{{--
    One setting: its label and a sentence on it, beside the control from md
    and above it below. The parent draws the rules between rows, as
    foundry:rows does or with `divide-y`.

    @group Forms
    @prop label The setting's name, a `<label>` where `for` names its control.
    @prop description A sentence on what the setting changes.
    @prop for The id of the control the label names.
    @slot slot The control.

    @example Two settings
    <div class="divide-y divide-zinc-200 border-y border-zinc-200 dark:divide-zinc-700 dark:border-zinc-700">
        <foundry:field-row label="Name" description="How your name appears on every agreement you send." for="name">
            <flux:input id="name" value="Ada Visser" />
        </foundry:field-row>
        <foundry:field-row label="Email" description="Where signing links and signed copies go." for="email">
            <flux:input id="email" type="email" value="ada@example.com" />
        </foundry:field-row>
    </div>
--}}
<div {{ $attributes->class('grid gap-3 py-6 md:grid-cols-[minmax(0,2fr)_minmax(0,3fr)] md:gap-12') }}>
    <div class="flex flex-col gap-1">
        @if ($for)
            <label for="{{ $for }}" class="font-medium text-zinc-950 dark:text-zinc-50">{{ $label }}</label>
        @else
            <p class="font-medium text-zinc-950 dark:text-zinc-50">{{ $label }}</p>
        @endif
        @if ($description)
            <foundry:text variant="small" tone="muted" class="max-w-[48ch]">{{ $description }}</foundry:text>
        @endif
    </div>
    <div class="min-w-0">
        {{ $slot }}
    </div>
</div>
