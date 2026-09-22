@props(['previous' => null, 'next' => null, 'back' => null])

{{--
    The foot of a long read: a note, and the ways on.

    @group Navigation
    @prop previous [label, href] of the page before, marked rel="prev".
    @prop next [label, href] of the page after, marked rel="next".
    @prop back [label, href] of the list the page belongs to: it leads up rather than along, and stands first.
    @slot slot The note, small and muted.

    @example Along
    <foundry:pager :previous="['Two dates', '#']" :next="['Two lanes', '#']">Checked against the API on 21 September 2026.</foundry:pager>

    @example Back
    <foundry:pager :back="['Every document', '#']">Questions: hello@example.com</foundry:pager>
--}}
<div {{ $attributes->class('flex flex-wrap items-center justify-between gap-x-6 gap-y-3 border-t border-zinc-200 dark:border-zinc-700 pt-6') }}>
    @if ($back)
        <a href="{{ $back[1] }}" class="flex items-center gap-1.5 font-medium text-zinc-950 dark:text-zinc-50 hover:text-primary-700 dark:hover:text-primary-300">
            <flux:icon.arrow-left variant="micro" class="fill-current" />
            {{ $back[0] }}
        </a>
    @endif
    <foundry:text variant="small" tone="muted">{{ $slot }}</foundry:text>
    @if ($previous || $next)
        <div class="flex flex-wrap gap-6">
            @if ($previous)
                <a href="{{ $previous[1] }}" rel="prev" class="flex items-center gap-1.5 font-medium text-zinc-950 dark:text-zinc-50 hover:text-primary-700 dark:hover:text-primary-300">
                    <flux:icon.arrow-left variant="micro" class="fill-current" />
                    {{ $previous[0] }}
                </a>
            @endif
            @if ($next)
                <a href="{{ $next[1] }}" rel="next" class="flex items-center gap-1.5 font-medium text-zinc-950 dark:text-zinc-50 hover:text-primary-700 dark:hover:text-primary-300">
                    {{ $next[0] }}
                    <flux:icon.arrow-right variant="micro" class="fill-current" />
                </a>
            @endif
        </div>
    @endif
</div>
