@props(['title'])

{{--
    One promise of foundry:promises: its number from the list's counter, its
    title and, from the slot, what it means.

    @prop title The promise, at size 2.
--}}

<div {{ $attributes->class('flex gap-6 border-b border-zinc-200 dark:border-zinc-700 py-6 [counter-increment:promise]') }}>
    <p class="w-6 shrink-0 pt-1 text-small font-medium text-zinc-600 dark:text-zinc-400 tabular-nums before:content-[counter(promise,decimal-leading-zero)]" aria-hidden="true"></p>
    <div class="flex flex-col gap-1.5">
        <dt><foundry:heading size="2">{{ $title }}</foundry:heading></dt>
        <dd class="max-w-[52ch] text-copy text-pretty">{{ $slot }}</dd>
    </div>
</div>
