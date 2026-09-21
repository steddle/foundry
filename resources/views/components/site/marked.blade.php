@props(['text', 'marked' => null])

{{-- `text` with the one phrase `marked` names laid on the marker, or plain where it names none it holds. --}}
@if ($marked && str_contains($text, $marked))
    {{ Str::before($text, $marked) }}<x-site.marker>{{ $marked }}</x-site.marker>{{ Str::after($text, $marked) }}
@else
    {{ $text }}
@endif
