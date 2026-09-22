@props(['text', 'marked' => null])

{{--
    `text` with the one phrase `marked` names laid on the lichen highlighter,
    or plain where `marked` names nothing `text` holds. Once per viewport,
    never on body copy or anything clickable. `inline-block leading-none`
    sizes the mark to the glyphs: an inline box is as tall as the face's
    content area, which at display sizes spills onto the next line.

    @group Type

    @example On a headline, on ink
    @ground ink
    <x-site.heading size="1"><x-site.marker text="A headline with one phrase marked." marked="one phrase marked." /></x-site.heading>
--}}
@if ($marked && str_contains($text, $marked))
    {{ Str::before($text, $marked) }}<mark {{ $attributes->class('relative z-0 inline-block bg-transparent leading-none whitespace-nowrap text-zinc-950 before:absolute before:-inset-x-[0.12em] before:-top-[0.05em] before:bottom-[0.06em] before:-z-10 before:-rotate-1 before:rounded-sm before:bg-secondary-300') }}>{{ $marked }}</mark>{{ Str::after($text, $marked) }}
@else
    {{ $text }}
@endif
