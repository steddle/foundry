@props(['author', 'quote' => null, 'resolved' => false, 'orphaned' => null, 'via' => null])

{{--
    One comment: who wrote it and when, the passage it is on, what it says,
    and its replies under it. A passage the text no longer holds shows as it
    was quoted, with where it stood.

    @group Elements
    @prop author Who wrote it, as copy names them.
    @prop quote The passage it is on; none for a comment on the whole.
    @prop resolved Whether its thread is closed: it reads muted.
    @prop orphaned Where the quote stood when the text no longer holds it, such as 'on version 4'.
    @prop via The agent it came through, such as 'Claude'.
    @slot time When it was written: an x-moment, a date.
    @slot actions Reply, resolve, remove.
    @slot replies The replies, each a foundry:comment.

    @example A comment on a passage, with a reply
    <foundry:comment author="Holly" quote="The fund closes in March.">
        <x-slot:time>2 hours ago</x-slot:time>
        Is March still right after the call with Winward?
        <x-slot:replies>
            <foundry:comment author="Mischa" via="Claude">
                <x-slot:time>1 hour ago</x-slot:time>
                May now. Suggested the change.
            </foundry:comment>
        </x-slot:replies>
    </foundry:comment>
--}}
<article {{ $attributes->class(['flex flex-col gap-2', 'opacity-70' => $resolved]) }} data-comment>
    <p class="text-base/7 text-zinc-600 sm:text-small dark:text-zinc-400">
        <span class="font-semibold text-zinc-950 dark:text-zinc-50">{{ $author }}</span>{{ $via ? " via {$via}" : '' }}@isset($time) | {{ $time }}@endisset{{ $resolved ? ' | resolved' : '' }}
    </p>

    @if ($quote)
        <blockquote class="border-l-2 border-warning-300 pl-3 text-base/7 text-zinc-700 sm:text-small dark:border-warning-300/60 dark:text-zinc-300">
            <p class="line-clamp-3">{{ $quote }}</p>
            @if ($orphaned)
                <p class="text-zinc-500 dark:text-zinc-400">No longer in the text, {{ $orphaned }}</p>
            @endif
        </blockquote>
    @endif

    <div class="whitespace-pre-line text-base/7 text-zinc-800 sm:text-small dark:text-zinc-200">{{ $slot }}</div>

    @isset($actions)
        <div class="flex flex-wrap gap-2">{{ $actions }}</div>
    @endisset

    @isset($replies)
        <div class="flex flex-col gap-4 border-l border-zinc-200 pl-4 dark:border-zinc-700">{{ $replies }}</div>
    @endisset
</article>
