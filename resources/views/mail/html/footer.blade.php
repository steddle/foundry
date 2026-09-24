@props(['line' => null, 'links' => [], 'legal' => null])
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
@if ($legal)
<p>{{ $legal }}</p>
@endif
</td>
</tr>
</table>
</td>
</tr>
