{{--
    Laravel's notification mail, opening on the foundry's greeting where the
    notification states none, and handing the foundry's theme what a
    notification gives it in `->markdown('notifications::email', ['mail' => [...]])`:
    `preheader`, `header`, `footer`, `links`, `legal`, and `above` and `below`
    as markdown.
--}}
@php($mail ??= [])
<x-mail::message :header="$mail['header'] ?? null" :footer="$mail['footer'] ?? null" :links="$mail['links'] ?? null" :legal="$mail['legal'] ?? null" :preheader="$mail['preheader'] ?? null">
@isset($mail['above'])
<x-slot:above>
{{ $mail['above'] }}
</x-slot:above>
@endisset

{{-- Greeting: the recipient by their first name, which the foundry's mail channel hands over. --}}
@if (! empty($greeting))
# {{ $greeting }}
@else
# {{ $foundryGreeting ?? \Steddle\Foundry\Mail\Greeting::for(null) }}
@endif

{{-- Intro Lines --}}
@foreach ($introLines as $line)
{{ $line }}

@endforeach

{{-- Action Button --}}
@isset($actionText)
<?php
    $color = match ($level) {
        'success', 'error' => $level,
        default => 'primary',
    };
?>
<x-mail::button :url="$actionUrl" :color="$color">
{{ $actionText }}
</x-mail::button>
@endisset

{{-- Outro Lines --}}
@foreach ($outroLines as $line)
{{ $line }}

@endforeach

{{-- Salutation --}}
@if (! empty($salutation))
{{ $salutation }}
@else
@lang('Regards,')<br>
{{ config('imprint.name', config('app.name')) }}
@endif

{{-- Subcopy --}}
@isset($actionText)
<x-slot:subcopy>
@lang(
    "If you're having trouble clicking the \":actionText\" button, copy and paste the URL below\n".
    'into your web browser:',
    [
        'actionText' => $actionText,
    ]
) <span class="break-all">[{{ $displayableActionUrl }}]({{ $actionUrl }})</span>
</x-slot:subcopy>
@endisset
@isset($mail['below'])
<x-slot:below>
{{ $mail['below'] }}
</x-slot:below>
@endisset
</x-mail::message>
