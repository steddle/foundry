@props(['url'])

@php
    // A PNG at twice its size, as `['src' => …, 'width' => …, 'height' => …]`: Gmail and Outlook draw no SVG, and Outlook sizes an image by its attributes alone.
    $lockup = config('imprint.mail.lockup');
    $src = $lockup === null ? null : (preg_match('#^https?://#', $lockup['src']) ? $lockup['src'] : asset($lockup['src']));
@endphp
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if ($slot->isNotEmpty())
{!! $slot !!}
@elseif ($src)
<img src="{{ $src }}" class="logo" width="{{ $lockup['width'] }}" height="{{ $lockup['height'] }}" alt="{{ config('imprint.name') }}">
@else
<span class="wordmark">{{ config('imprint.name') }}</span>
@endif
</a>
</td>
</tr>
