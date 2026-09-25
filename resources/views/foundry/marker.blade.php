@props(['text', 'marked' => null])

{{--
    One phrase of a headline laid on the lichen highlighter. Once per
    viewport, never on body copy or anything clickable. An inline block
    with no leading sizes the mark to the glyphs: an inline box is as tall as
    the face's content area, which at display sizes spills onto the next
    line.

    @group Type
    @prop text The whole line.
    @prop marked The phrase of `text` to mark, at its first occurrence; where `text` does not hold it, or it is empty, the line is plain.

    @example On a headline, on ink
    @ground ink
    <foundry:heading size="1"><foundry:marker text="A headline with one phrase marked." marked="one phrase marked." /></foundry:heading>
--}}
@if ($marked && str_contains($text, $marked))
    {{ Str::before($text, $marked) }}<mark {{ $attributes->class('relative z-0 inline-block bg-transparent leading-none whitespace-nowrap text-zinc-950 before:absolute before:-inset-x-[0.12em] before:-top-[0.05em] before:bottom-[0.06em] before:-z-10 before:-rotate-1 before:rounded-sm before:bg-secondary-300') }}>{{ $marked }}</mark>{{ Str::after($text, $marked) }}
@else
    {{ $text }}
@endif
