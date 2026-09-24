@props(['line' => null, 'links' => [], 'legal' => null, 'service' => false])

@php
    $steddle = $service ? \Steddle\Foundry\Mail\MailOptions::image('mail-steddle-logo') : null;
@endphp
<tr>
<td>
<table class="footer" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td class="content-cell" align="center">
{{-- A message of the imprint's own that fills the footer as Laravel's does. --}}
@if ($slot->isNotEmpty())
{{ Illuminate\Mail\Markdown::parse($slot) }}
@endif
@if ($line)
<p>{{ $line }}</p>
@endif
@if ($links)
<p>
@foreach ($links as $label => $href)
<a href="{{ $href }}">{{ $label }}</a>@unless ($loop->last) | @endunless
@endforeach
</p>
@endif
@if ($legal || $service)
{{-- Steddle in its own face, which only Apple Mail would load as a font: an image elsewhere. --}}
<p>{{ $legal }}@if ($legal && $service) | @endif @if ($service){{ __('foundry::footer.service_by') }} @if ($steddle)<img src="{{ $steddle }}" class="wordmark-image" width="50" height="18" alt="Steddle">@else<span class="service">Steddle</span>@endif @endif</p>
@endif
</td>
</tr>
</table>
</td>
</tr>
