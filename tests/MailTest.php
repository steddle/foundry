<?php

use Illuminate\Mail\Markdown;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\File;
use Steddle\Foundry\Mail\MailOptions;

beforeEach(function () {
    config([
        'imprint.name' => 'Imprint',
        'imprint.ink' => '#0b231c',
        'imprint.paper' => '#f1f2ea',
        'imprint.disclaimer' => 'Imprint is not a law firm.',
        'imprint.mail' => [],
        'app.url' => 'https://imprint.test',
    ]);
});

afterEach(function () {
    File::deleteDirectory(resource_path('views/vendor'));
    File::delete(resource_path('views/mail-probe.blade.php'));
    $this->artisan('view:clear');
});

/** The mail as a reader reads it: its text, tags gone, whitespace collapsed. */
function readable(string $html): string
{
    return (string) str(html_entity_decode(strip_tags($html), ENT_QUOTES))->squish();
}

function notification(array $mail = []): string
{
    $message = (new MailMessage)->subject('Signed')->greeting('Dear Ada,')->line('Both sides have signed.')->action('View the agreement', 'https://imprint.test/nda');

    return (string) ($mail === [] ? $message : $message->markdown('notifications::email', ['mail' => $mail]))->render();
}

test('a mail with no mail settings sets the imprint\'s name over the message, and its disclaimer, copyright and house under it', function () {
    $html = notification();

    expect($html)
        ->toContain('Both sides have signed.')
        ->toMatch('#<span class="wordmark"[^>]*>Imprint</span>#')
        ->toMatch('#<body[^>]*background-color: \#f1f2ea#')
        ->toMatch('#class="button button-primary"[^>]*background-color: \#0b231c;[^"]*color: \#f1f2ea;#')
        ->toContain('Imprint is not a law firm.')
        ->toMatch('#<span class="service"[^>]*>Steddle</span>#')
        ->not->toContain('class="preheader"')
        ->and(readable($html))->toContain('© '.date('Y').' Imprint | A service by Steddle');
});

test('the imprint\'s mail settings set the lockup, the accent, the footer, its links and the legal line', function () {
    config(['imprint.mail' => [
        'lockup' => ['src' => 'brand/logos/lockup-endorsed@2x.png', 'width' => 240, 'height' => 47],
        'accent' => '#f2e96b',
        'footer' => 'Sent by Imprint.',
        'links' => ['Privacy' => 'https://imprint.test/privacy', 'Terms' => 'https://imprint.test/terms'],
        'legal' => 'Imprint B.V. | Amsterdam',
    ]]);

    $html = notification();

    expect($html)
        ->toMatch('#<img src="https?://[^"/]+/brand/logos/lockup-endorsed@2x.png" class="logo"[^>]* width="240" height="47" alt="Imprint"#')
        ->not->toContain('class="wordmark"')
        ->toMatch('#class="button button-primary"[^>]*background-color: \#f2e96b;[^"]*color: \#0b231c;#')
        ->toContain('Sent by Imprint.')
        ->toMatch('#>Privacy</a> \|\s*<a href="https://imprint.test/terms"#')
        ->toContain('Imprint B.V. | Amsterdam')
        ->not->toContain('Imprint is not a law firm.');
});

test('a notification sets its own preheader, header, footer and blocks over what the imprint states', function () {
    config(['imprint.mail' => ['footer' => 'Sent by Imprint.']]);

    $html = notification([
        'preheader' => 'Your NDA is signed.',
        'header' => 'none',
        'footer' => false,
        'legal' => 'Imprint B.V.',
        'above' => 'A notice over the message.',
        'below' => 'A block under it.',
    ]);

    expect($html)
        ->toMatch('#<div class="preheader"[^>]*>Your NDA is signed\.#')
        ->not->toContain('class="header"')
        ->not->toContain('Sent by Imprint.')
        ->toContain('Imprint B.V.')
        ->toMatch('#<div class="block"[^>]*>\s*<p[^>]*>A notice over the message\.</p>#')
        ->toMatch('#<div class="block-below"[^>]*>\s*<p[^>]*>A block under it\.</p>#')
        ->and(strpos($html, 'A notice over the message.'))->toBeLessThan(strpos($html, 'Both sides have signed.'))
        ->and(strpos($html, 'A block under it.'))->toBeGreaterThan(strpos($html, 'Both sides have signed.'));
});

test('a markdown mailable sets the options as props and the blocks as slots', function () {
    File::put(resource_path('views/mail-probe.blade.php'), <<<'BLADE'
        <x-mail::message preheader="Pre" :links="['Help' => 'https://imprint.test/help']" legal="Imprint B.V.">
        <x-slot:above>Above.</x-slot:above>
        The body.
        <x-mail::panel>A panel.</x-mail::panel>
        </x-mail::message>
        BLADE);

    $html = (string) app(Markdown::class)->render('mail-probe');

    expect($html)
        ->toMatch('#<div class="preheader"[^>]*>Pre#')
        ->toContain('The body.')
        ->toMatch('#class="panel-content"[^>]*background-color: \#[0-9a-f]{6}#')
        ->toContain('>Help</a>')
        ->toContain('Imprint B.V.')
        ->toContain('Imprint is not a law firm.');
});

test('the plain-text part carries the name, the body and the footer lines', function () {
    config(['imprint.mail' => ['links' => ['Privacy' => 'https://imprint.test/privacy']]]);

    $text = (string) app(Markdown::class)->renderText('notifications::email', (new MailMessage)->line('Both sides have signed.')->action('View', 'https://imprint.test/nda')->data());

    expect($text)
        ->toStartWith('Imprint')
        ->toContain('Both sides have signed.')
        ->toContain('Imprint is not a law firm.')
        ->toContain('Privacy: https://imprint.test/privacy')
        ->toContain('© '.date('Y').' Imprint | A service by Steddle');
});

test('an imprint\'s own mail files stand in for the foundry\'s, a component and a notification alike', function () {
    File::ensureDirectoryExists(resource_path('views/vendor/mail/html'));
    File::put(resource_path('views/vendor/mail/html/header.blade.php'), '<tr><td>The imprint\'s own header</td></tr>');
    $this->artisan('view:clear');

    expect(notification())->toContain('The imprint\'s own header')->toContain('Imprint is not a law firm.');

    File::ensureDirectoryExists(resource_path('views/vendor/notifications'));
    File::put(resource_path('views/vendor/notifications/email.blade.php'), '<x-mail::message>The imprint\'s own notification.</x-mail::message>');
    $this->refreshApplication();
    config(['imprint.name' => 'Imprint', 'imprint.disclaimer' => 'Imprint is not a law firm.']);

    expect(notification())->toContain('The imprint\'s own notification.')->not->toContain('Both sides have signed.');
});

test('without a footer or legal line from anyone, the defaults hold, and false leaves each out', function () {
    expect(MailOptions::resolve())->toBe([
        'header' => 'lockup',
        'footer' => 'Imprint is not a law firm.',
        'links' => [],
        'legal' => '© '.date('Y').' Imprint',
        'service' => true,
    ])
        ->and(MailOptions::resolve(footer: false, legal: false))->toMatchArray(['footer' => null, 'legal' => null, 'service' => false])
        ->and(MailOptions::resolve(legal: 'Imprint B.V. | Amsterdam'))->toMatchArray(['legal' => 'Imprint B.V. | Amsterdam', 'service' => false]);

    config(['imprint.endorsed' => false]);

    expect(MailOptions::resolve())->toMatchArray(['legal' => '© '.date('Y').' Imprint', 'service' => false]);
});

test('the sample mail renders at /foundry/mail, kept out of search, and as plain text', function () {
    $this->get('/foundry/mail')->assertOk()->assertHeader('X-Robots-Tag', 'noindex')->assertSee('Your agreement is signed')->assertSee('Imprint is not a law firm.');
    $this->get('/foundry/mail?text')->assertOk()->assertHeader('Content-Type', 'text/plain; charset=UTF-8')->assertSee('View the agreement');
});

test('an apostrophe or a quote in the imprint\'s words reaches the mail as written', function () {
    config(['imprint.name' => 'Ada\'s "Desk"', 'imprint.disclaimer' => 'Ada\'s desk gives no advice.']);

    expect(html_entity_decode(notification(), ENT_QUOTES))->toContain('Ada\'s "Desk"')->toContain('Ada\'s desk gives no advice.');
});

test('a message of the imprint\'s own that fills the header and footer as Laravel\'s does keeps what it put there', function () {
    File::ensureDirectoryExists(resource_path('views/vendor/mail/html'));
    File::put(resource_path('views/vendor/mail/html/message.blade.php'), <<<'BLADE'
        <x-mail::layout>
        <x-slot:header><x-mail::header url="https://imprint.test">The imprint's own name</x-mail::header></x-slot:header>
        {!! $slot !!}
        <x-slot:footer><x-mail::footer>The imprint's own footer.</x-mail::footer></x-slot:footer>
        </x-mail::layout>
        BLADE);
    $this->artisan('view:clear');

    expect(notification())->toContain('The imprint\'s own name')->toContain('The imprint\'s own footer.')->not->toContain('class="wordmark"');
});

test('the salutation signs with the imprint\'s name', function () {
    config(['app.name' => 'Laravel']);

    expect(notification())->toMatch('#Regards,<br>\s*Imprint#');
});

test('once foundry:assets has rendered them, the header draws the imprint\'s lockup and the footer Steddle\'s wordmark, each marked with its version', function () {
    File::ensureDirectoryExists(public_path('brand/mail'));
    File::put(public_path('brand/mail/logo-2x.png'), 'PNG');
    File::put(public_path('brand/mail/steddle-logo-2x.png'), 'PNG');
    File::put(public_path('brand/assets.json'), json_encode(['mail-logo' => 'abcdef1234567890', 'mail-steddle-logo' => '0123456789abcdef']));

    try {
        $html = notification();
    } finally {
        File::deleteDirectory(public_path('brand'));
    }

    expect($html)
        ->toMatch('#<img src="[^"]*/brand/mail/logo-2x\.png\?v=abcdef12" class="logo"[^>]* width="240" height="48" alt="Imprint"#')
        ->toMatch('#A service by <img src="[^"]*/brand/mail/steddle-logo-2x\.png\?v=01234567" class="wordmark-image"[^>]* width="50" height="18" alt="Steddle"#')
        ->not->toContain('class="wordmark"')
        ->not->toContain('class="service"');
});

test('the imprint\'s own lockup setting comes before the rendered one', function () {
    config(['imprint.mail.lockup' => ['src' => 'brand/logos/own-2x.png', 'width' => 200, 'height' => 40]]);
    File::ensureDirectoryExists(public_path('brand/mail'));
    File::put(public_path('brand/mail/logo-2x.png'), 'PNG');

    try {
        expect(notification())->toContain('/brand/logos/own-2x.png"')->not->toContain('brand/mail/logo-2x.png');
    } finally {
        File::deleteDirectory(public_path('brand'));
    }
});

test('the address under the button is set as small and quiet as the footer', function () {
    expect(notification())->toMatch('#<table class="subcopy".*?<p style="[^"]*color: \#738079; font-size: 12px; line-height: 1\.5;#s');
});

class MailRecipient
{
    use Notifiable;

    public function __construct(public ?string $name, public string $email = 'ada@imprint.test') {}
}

function greetingSent(object $notifiable, ?string $greeting = null, string $locale = 'en'): string
{
    config(['mail.default' => 'array']);

    $notifiable->notify((new class($greeting) extends Notification
    {
        public function __construct(private ?string $greeting) {}

        public function via(object $notifiable): array
        {
            return ['mail'];
        }

        public function toMail(object $notifiable): MailMessage
        {
            return tap((new MailMessage)->subject('Signed')->line('Both sides have signed.'), fn (MailMessage $message) => $this->greeting && $message->greeting($this->greeting));
        }
    })->locale($locale));

    $html = app('mailer')->getSymfonyTransport()->messages()->last()->getOriginalMessage()->getHtmlBody();

    return (string) str($html)->match('#<h1[^>]*>(.*?)</h1>#s');
}

test('a notification without a greeting of its own greets its recipient by first name, or without a name where they gave none', function (?string $name, string $locale, string $greeting) {
    expect(greetingSent(new MailRecipient($name), locale: $locale))->toBe($greeting);
})->with([
    'a name' => ['Ada Visser', 'en', 'Hi Ada,'],
    'a name, in Dutch' => ['Ada Visser', 'nl', 'Hoi Ada,'],
    'the local part of the address' => ['ada', 'en', 'Hi there,'],
    'no name' => [null, 'en', 'Hi there,'],
    'no name, in Dutch' => [null, 'nl', 'Hallo daar,'],
]);

test('a notification that states its greeting keeps it', function () {
    expect(greetingSent(new MailRecipient('Ada Visser'), 'Dear Ada,'))->toBe('Dear Ada,');
});

test('/design shows the mail as it renders, in a frame of its own, and its plain-text part, under the number the page gives it', function () {
    $html = view('foundry::design.mail', ['number' => '13'])->render();

    expect($html)
        ->toContain('13')
        ->toContain('>Mail<')
        ->toMatch('#<iframe srcdoc="[^"]*Your agreement is signed[^"]*"#')
        ->toContain('&lt;span class=&quot;wordmark&quot;')
        ->and(readable((string) str($html)->after('</iframe>')))->toContain('# Your agreement is signed')->toContain('View the agreement: ');
});

test('a name the recipient confirmed greets them, though it is the local part of their address', function () {
    $recipient = new class('Ada') extends MailRecipient
    {
        public function hasOnboarded(): bool
        {
            return true;
        }
    };

    expect(greetingSent($recipient))->toBe('Hi Ada,');
});
