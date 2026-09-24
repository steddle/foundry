# Mail

Every markdown mail {{ config('imprint.name') }} sends, a mailable's `x-mail::message` and a notification's `MailMessage` alike, renders through the foundry's theme, which the foundry registers itself: nothing to publish. It sets the imprint's lockup over the message, a sheet on its paper, buttons in its accent or ink, and a footer of its words, with Steddle in its wordmark for an endorsed imprint. Sending is {{ config('imprint.name') }}'s own: `config/mail.php` names the mailer, Resend or another, and `MAIL_FROM_ADDRESS` the sender. The foundry requires no transport.

## What the imprint states

Under `mail` in `config/imprint.php`, every key optional; an imprint without one sends a well-set mail on its name, ink, paper and disclaimer.

| Key | What it sets | Without it |
|---|---|---|
| `header` | `lockup` over the message, or `none` | `lockup` |
| `lockup` | `['src' => …, 'width' => …, 'height' => …]`: a PNG, a path under `public/` or a URL, drawn at `width` by `height`, half its pixels. Gmail and Outlook draw no SVG | `brand/mail/logo-2x.png`, which `foundry:assets` renders; before it has, the imprint's name, set in Spectral |
| `accent` | The primary button's fill, six-digit hex, under ink | Ink, under paper |
| `footer` | The line under the message, a translation key where the site speaks more than one language; `false` for none | `imprint.disclaimer` |
| `links` | label => url under the footer line | None |
| `legal` | The last line: an address, a registration; `false` for none | The copyright and, for an endorsed imprint, A service by Steddle, Steddle drawn from `brand/mail/steddle-logo-2x.png` |

The colours come from `imprint.ink` and `imprint.paper`, six-digit hex; the tones between them are mixed as hex, since mail clients mix nothing.

## The images

Mail clients draw no SVG, and only Apple Mail loads a web font, so the lockup and Steddle's wordmark go into a mail as PNGs. `php artisan foundry:assets` renders them with the rest of the published images, on the mail's paper at twice their size: `public/brand/mail/logo-2x.png`, the imprint's lockup, endorsed where it is, and for an endorsed imprint `public/brand/mail/steddle-logo-2x.png`. Commit both; `foundry:assets --check` holds them to the copy and the colours. A mail links them with their version, so an inbox fetches them anew once they change.

No `@` in the name of an image a mail links to, or of any published file: Laravel Cloud's edge answers 404 to a path with an unencoded `@`, and a mail client asks for it unencoded. `lockup-endorsed-2x.png`, never `lockup-endorsed@2x.png`.

## The greeting

A notification that states no greeting of its own opens on its recipient's first name, `Hi Ada,`, `Hoi Ada,` in Dutch, or on `Hi there,`, `Hallo daar,`, where the recipient has none: no name, or the local part of the address, as an account that has not onboarded carries. The foundry's mail channel hands the notification's view the recipient; `->greeting()` still sets any line of the notification's own.

## Per mailable

A markdown mailable sets the same options as props, and a block over or under the message as slots:

```blade
<x-mail::message preheader="Your NDA is signed." :links="['Help' => route('help')]">
<x-slot:above>A notice over the message.</x-slot:above>

# Your agreement is signed

<x-mail::button :url="$url">View the agreement</x-mail::button>
</x-mail::message>
```

`preheader` is the line an inbox shows beside the subject. `footer` and `legal` take `false` to leave the line out, and `header="none"` leaves the lockup off.

A notification hands them over with Laravel's own view:

```php
(new MailMessage)
    ->subject('Your NDA is signed')
    ->line('Both sides have signed.')
    ->action('View the agreement', $url)
    ->markdown('notifications::email', ['mail' => ['preheader' => 'Your NDA is signed.', 'below' => 'A block under the message.']]);
```

`above` and `below` are markdown there.

## Standing in

A file under `resources/views/vendor/mail/html` or `text`, or `resources/views/vendor/notifications/email.blade.php`, stands in for the foundry's of that name. Keep one only where the theme cannot say it; a colour or a line belongs in `imprint.mail`. A `markdown` block in `config/mail.php` keeps its `paths` key, or the imprint's own mail files are no longer read.

## Seeing it

`/foundry/mail` renders a sample through the theme with {{ config('imprint.name') }}'s values, every component Laravel's markdown mail has; `/foundry/mail?text` shows its plain-text part. `/design` shows both, under Mail. The site's suite renders a notification through the theme and checks the name, the footer and the greeting.
