@props(['steps'])

{{--
    Steps in order on a Flux timeline: a check on each one done, the current
    one ringed, the rest muted. Below sm the row scrolls sideways and opens on
    the current step.

    @group Elements
    @prop steps A list of steps, each `label`, `meta` (a line under it, optional), `status` (complete, current or incomplete), `icon` (a Heroicon for the indicator in place of its number, optional) and `click` (a wire:click for a step that can be opened, optional).

    @example An NDA waiting on the sender
    <x-site.steps :steps="[
        ['label' => 'Sent', 'meta' => '21 Sep, 10:42 UTC', 'status' => 'complete'],
        ['label' => 'Viewed', 'meta' => '21 Sep, 10:44 UTC', 'status' => 'complete'],
        ['label' => 'Signed', 'meta' => 'Sam signed 10:46', 'status' => 'complete'],
        ['label' => 'Countersign', 'meta' => 'Your turn', 'status' => 'current'],
        ['label' => 'Completed', 'meta' => 'Both get the PDF', 'status' => 'incomplete'],
    ]" />
--}}
<div {{ $attributes->class('-mx-4 overflow-x-auto px-4 [scrollbar-width:none]') }} x-data x-init="$el.querySelector('[data-flux-timeline-status=current]')?.scrollIntoView({ block: 'nearest', inline: 'center' })">
    <flux:timeline horizontal class="min-w-max [--flux-timeline-item-gap:2.5rem]">
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
                <flux:timeline.content>
                    @if (isset($step['click']))
                        <button type="button" wire:click="{{ $step['click'] }}" @if ($step['status'] === 'current') aria-current="step" @endif class="group cursor-pointer text-center">
                            <span class="block text-small font-semibold text-zinc-950 dark:text-zinc-50">{{ $step['label'] }}</span>
                            @if (filled($step['meta'] ?? null))
                                <span class="block text-small text-zinc-600 dark:text-zinc-400 group-hover:underline">{{ $step['meta'] }}</span>
                            @endif
                        </button>
                    @else
                        <div @if ($step['status'] === 'current') aria-current="step" @endif class="text-center">
                            <span class="block text-small font-semibold text-zinc-950 dark:text-zinc-50">{{ $step['label'] }}</span>
                            @if (filled($step['meta'] ?? null))
                                <span class="block text-small text-zinc-600 dark:text-zinc-400 tabular-nums">{{ $step['meta'] }}</span>
                            @endif
                        </div>
                    @endif
                </flux:timeline.content>
            </flux:timeline.item>
        @endforeach
    </flux:timeline>
</div>
