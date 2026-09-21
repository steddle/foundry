@props(['text', 'marked' => null])

{{--
    `text` with the one phrase `marked` names laid on the marker, or plain
    where it names none it holds.

    @group Type

    @example On a heading, on ink
    @ground ink
    <x-site.heading size="1"><x-site.marked text="A headline with one phrase marked." marked="one phrase marked." /></x-site.heading>
--}}
@if ($marked && str_contains($text, $marked))
    {{ Str::before($text, $marked) }}<x-site.marker>{{ $marked }}</x-site.marker>{{ Str::after($text, $marked) }}
@else
    {{ $text }}
@endif
