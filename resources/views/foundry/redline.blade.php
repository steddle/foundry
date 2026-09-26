@use('Steddle\Foundry\Diff\WordDiff')

@props(['before', 'after'])

{{--
    One text changed into another, word by word: what went struck through on
    the danger wash, what came on the success wash, what stayed as it was.
    Paragraph breaks stay breaks.

    @group Elements
    @prop before The text as it was.
    @prop after The text as it is to be.

    @example A changed sentence
    <foundry:redline before="The fund closes in March, with two founders." after="The fund closes in May, with two founders and an advisor." />
--}}
<div {{ $attributes->class('flex flex-col gap-3 text-base/7 text-zinc-800 sm:text-small dark:text-zinc-200') }} data-redline>
    @foreach (WordDiff::paragraphs($before, $after) as $runs)
        <p class="text-pretty">
            @foreach ($runs as [$type, $text])
                @if ($type === 'removed')
                    <del class="bg-danger-50 text-danger-700 decoration-danger-700 dark:bg-danger-300/15 dark:text-danger-300 dark:decoration-danger-300">{{ $text }}</del>
                @elseif ($type === 'added')
                    <ins class="bg-success-50 text-success-700 no-underline dark:bg-success-300/15 dark:text-success-300">{{ $text }}</ins>
                @else
                    {{ $text }}
                @endif
            @endforeach
        </p>
    @endforeach
</div>
