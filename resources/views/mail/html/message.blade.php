@props(['header' => null, 'footer' => null, 'links' => null, 'legal' => null, 'preheader' => null])

{{--
    The foundry's markdown mail, which every imprint's `x-mail::message`
    renders through without publishing it: FoundryServiceProvider adds this
    folder to `mail.markdown.paths` after the imprint's own, so a file the
    imprint keeps under resources/views/vendor/mail still stands in for one
    here; an imprint that sets `mail.markdown` in its config keeps `paths`
    in it, or its own files are no longer read. Sending stays the
    imprint's: its `config/mail.php` names the mailer, Resend or another,
    and the foundry requires no transport.

    Each option is read from `imprint.mail`, and a prop here sets it for one
    mailable; a notification hands them over as
    `->markdown('notifications::email', ['mail' => [...]])`.

    @prop header `lockup` for the imprint's lockup over the message, `none` for no header.
    @prop footer The line under the message; without one, the imprint's disclaimer. False leaves it out.
    @prop links label => url, set under the footer line.
    @prop legal The last line: an address, a registration; without one, the copyright and, for an endorsed imprint, the house that serves it. False leaves it out.
    @prop preheader The line an inbox shows beside the subject, hidden in the message itself.
    @slot above A block over the message, such as a notice.
    @slot below A block under the message and its subcopy, over a rule.
--}}
@php
    ['header' => $header, 'footer' => $footer, 'links' => $links, 'legal' => $legal] = \Steddle\Foundry\Mail\MailOptions::resolve($header, $footer, $links, $legal);
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
<x-mail::footer :line="$footer" :$links :$legal />
</x-slot:footer>
</x-mail::layout>
