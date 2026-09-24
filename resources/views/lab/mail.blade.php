{{-- The sample /foundry/mail renders: every component of Laravel's markdown mail, through the foundry's theme. --}}
<x-mail::message preheader="What the inbox shows beside the subject, and nowhere in the message.">
<x-slot:above>
A notice over the message, from the mailable's `above` slot.
</x-slot:above>

# Your agreement is signed

Both sides have signed the mutual NDA with Northwind B.V. The signed copy and its certificate are ready.

<x-mail::button :url="config('app.url')">
View the agreement
</x-mail::button>

<x-mail::panel>
The certificate records who signed, when, and the document's hash.
</x-mail::panel>

<x-mail::table>
| Party | Signed |
|:--|--:|
| Ada Visser | 21 Sep 2026, 10:37 |
| Northwind B.V. | 21 Sep 2026, 11:02 |
</x-mail::table>

Best regards,<br>
{{ config('imprint.name') }}

<x-slot:subcopy>
If the button does not open, copy this address into your browser: {{ config('app.url') }}
</x-slot:subcopy>

<x-slot:below>
A block under the message, from the mailable's `below` slot.
</x-slot:below>
</x-mail::message>
