@props(['url', 'client' => null])

@php
    // A PNG at twice its size, as `['src' => …, 'width' => …, 'height' => …]`: Gmail and Outlook draw no SVG, and Outlook sizes an image by its attributes alone.
    // Without one, the lockup foundry:assets renders, once the imprint has rendered it; for a client, the one it published.
    $lockup = isset($client['icons']) ? ['src' => rtrim($client['icons'], '/').'/brand/mail/logo-2x.png', 'width' => 240, 'height' => 48] : config('imprint.mail.lockup');
    $rendered = $lockup === null ? \Steddle\Foundry\Mail\MailOptions::image('mail-logo') : null;
    $lockup ??= $rendered === null ? null : ['src' => $rendered, 'width' => 240, 'height' => 48];
    $src = $lockup === null ? null : (preg_match('#^https?://#', $lockup['src']) ? $lockup['src'] : asset($lockup['src']));
@endphp
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if ($slot->isNotEmpty())
{!! $slot !!}
@elseif ($src)
<img src="{{ $src }}" class="logo" width="{{ $lockup['width'] }}" height="{{ $lockup['height'] }}" alt="{{ $client['name'] ?? config('imprint.name') }}">
@else
<span class="wordmark">{{ $client['name'] ?? config('imprint.name') }}</span>
@endif
</a>
</td>
</tr>
