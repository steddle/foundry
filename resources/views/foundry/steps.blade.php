@props(['steps'])

{{--
    Steps in order on a Flux timeline: a check on each one done, the current
    one ringed, the rest muted. Below sm the row scrolls sideways and opens on
    the current step.

    @group Elements
    @prop steps A list of steps, each `label`, `meta` (a line under it, optional), `status` (complete, current or incomplete), `icon` (a Heroicon for the indicator in place of its number, optional) and `click` (a wire:click for a step that can be opened, optional).

    @example An NDA waiting on the sender
    <foundry:steps :steps="[
        ['label' => 'Sent', 'meta' => '21 Sep, 10:42 UTC', 'status' => 'complete'],
        ['label' => 'Viewed', 'meta' => '21 Sep, 10:44 UTC', 'status' => 'complete'],
        ['label' => 'Signed', 'meta' => 'Sam signed 10:46', 'status' => 'complete'],
        ['label' => 'Countersign', 'meta' => 'Your turn', 'status' => 'current'],
        ['label' => 'Completed', 'meta' => 'Both get the PDF', 'status' => 'incomplete'],
    ]" />
--}}
<div {{ $attributes->class('-mx-4 overflow-x-auto px-4 [scrollbar-width:none]') }} x-data x-init="$el.querySelector('[data-flux-timeline-status=current]')?.scrollIntoView({ block: 'nearest', inline: 'center' })">
    {{-- Equal columns, so a step whose words change never moves the others; fixed below sm, so the row is as wide as its steps and the scroll keeps its end padding. --}}
    <flux:timeline horizontal class="w-max [grid-auto-columns:10rem] [--flux-timeline-item-gap:1rem] sm:w-auto sm:[grid-auto-columns:minmax(10rem,1fr)]">
        @foreach ($steps as $index => $step)
            <flux:timeline.item :status="$step['status']">
                {{-- Flux rings an open step in zinc-100, which a sunken band hides. --}}
                <flux:timeline.indicator class="[[data-flux-timeline-status=incomplete]_&]:border-zinc-300! dark:[[data-flux-timeline-status=incomplete]_&]:border-zinc-600!">
                    @if ($step['status'] === 'complete')
                        <flux:icon.check variant="micro" />
                    @elseif (isset($step['icon']))
                        <flux:icon :icon="$step['icon']" variant="micro" />
                    @else
                        {{ $index + 1 }}
                    @endif
                </flux:timeline.indicator>
                <flux:timeline.content class="w-full">
                    @if (isset($step['click']))
                        <flux:button variant="subtle" :loading="false" wire:click="{{ $step['click'] }}" :aria-current="$step['status'] === 'current' ? 'step' : null" class="group flex! h-auto! w-full min-w-0 cursor-pointer flex-col items-stretch! gap-0! px-0! text-center hover:bg-transparent!">
                            <span class="block truncate text-small font-semibold text-zinc-950 dark:text-zinc-50">{{ $step['label'] }}</span>
                            @if (filled($step['meta'] ?? null))
                                <span class="block truncate text-small font-normal text-zinc-600 dark:text-zinc-400 group-hover:underline">{{ $step['meta'] }}</span>
                            @endif
                        </flux:button>
                    @else
                        <div @if ($step['status'] === 'current') aria-current="step" @endif class="min-w-0 text-center">
                            <span class="block truncate text-small font-semibold text-zinc-950 dark:text-zinc-50">{{ $step['label'] }}</span>
                            @if (filled($step['meta'] ?? null))
                                <span class="block truncate text-small text-zinc-600 dark:text-zinc-400 tabular-nums">{{ $step['meta'] }}</span>
                            @endif
                        </div>
                    @endif
                </flux:timeline.content>
            </flux:timeline.item>
        @endforeach
    </flux:timeline>
</div>
