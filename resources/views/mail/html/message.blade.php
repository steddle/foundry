@props(['header' => null, 'footer' => null, 'links' => null, 'legal' => null, 'preheader' => null])

{{--
    An imprint that sets `mail.markdown` in its config keeps `paths` in it, or
    its own files under resources/views/vendor/mail are no longer read.

    Each option is read from `imprint.mail`, and a prop here sets it for one
    mailable; a notification hands them over as
    `->markdown('notifications::email', ['mail' => [...]])`.

    @prop header `lockup` for the imprint's lockup over the message, `none` for no header. The lockup is `imprint.mail.lockup`, else the one foundry:assets renders to brand/mail/logo-2x.png, else the imprint's name.
    @prop footer The line under the message; without one, the imprint's disclaimer. False leaves it out.
    @prop links label => url, set under the footer line.
    @prop legal The last line: an address, a registration; without one, the copyright and, for an endorsed imprint, the house that serves it, Steddle drawn as its wordmark once foundry:assets has rendered it. False leaves it out.
    @prop preheader The line an inbox shows beside the subject, hidden in the message itself.
    @slot above A block over the message, such as a notice.
    @slot below A block under the message and its subcopy, over a rule.
--}}
@php
    ['header' => $header, 'footer' => $footer, 'links' => $links, 'legal' => $legal, 'service' => $service] = \Steddle\Foundry\Mail\MailOptions::resolve($header, $footer, $links, $legal);
@endphp
<x-mail::layout :$preheader>
@if ($header !== 'none')
<x-slot:header>
<x-mail::header :url="config('app.url')" />
</x-slot:header>
@endif

@isset($above)
<x-slot:above>
{!! $above !!}
</x-slot:above>
@endisset

{!! $slot !!}

@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{!! $subcopy !!}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

@isset($below)
<x-slot:below>
{!! $below !!}
</x-slot:below>
@endisset

<x-slot:footer>
<x-mail::footer :line="$footer" :$links :$legal :$service />
</x-slot:footer>
</x-mail::layout>
