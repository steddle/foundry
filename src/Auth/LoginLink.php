<?php

namespace Steddle\Foundry\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Laravel\Passkeys\Contracts\PasskeyUser;

/**
 * Queued on `imprint.auth.queue` where the imprint names one: a link that
 * waits behind a long job has expired before it arrives.
 */
class LoginLink extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $url) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /** @return array<string, ?string> */
    public function viaQueues(): array
    {
        return ['mail' => config('imprint.auth.queue')];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $name = config('imprint.name');

        return (new MailMessage)
            ->subject(__('foundry::sign-in.mail.subject', ['name' => $name]))
            ->line(__('foundry::sign-in.mail.intro', ['name' => $name, 'minutes' => MagicLink::MINUTES]))
            ->action(__('foundry::sign-in.mail.action'), $this->url)
            ->when($notifiable instanceof PasskeyUser && ! $notifiable->passkeys()->exists(), fn (MailMessage $mail) => $mail->line(__('foundry::sign-in.mail.passkey')))
            ->line(__('foundry::sign-in.mail.ignore'));
    }
}
