@props(['header' => null, 'footer' => null, 'links' => null, 'legal' => null, 'preheader' => null])

@php
    ['header' => $header, 'footer' => $footer, 'links' => $links, 'legal' => $legal, 'service' => $service] = \Steddle\Foundry\Mail\MailOptions::resolve($header, $footer, $links, $legal);
@endphp
<x-mail::layout>
@if ($header !== 'none')
<x-slot:header>
{{ config('imprint.name') }}
</x-slot:header>
@endif

@isset($above)
{{ $above }}

@endisset
{{ $slot }}
@isset($subcopy)

{{ $subcopy }}
@endisset
@isset($below)

{{ $below }}
@endisset

<x-slot:footer>
@if ($footer)
{{ $footer }}

@endif
@foreach ($links as $label => $href)
{{ $label }}: {{ $href }}
@endforeach
@if ($legal || $service)

{{ implode(' | ', array_filter([$legal, $service ? __('foundry::footer.service_by').' Steddle' : null])) }}
@endif
</x-slot:footer>
</x-mail::layout>
