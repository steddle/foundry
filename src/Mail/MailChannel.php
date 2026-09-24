<?php

namespace Steddle\Foundry\Mail;

use Illuminate\Notifications\Channels\MailChannel as LaravelMailChannel;
use Illuminate\Notifications\Notification;

/**
 * Laravel's mail channel, handing the foundry's notification view the
 * greeting for the one it sends to, which Laravel's own never passes it.
 */
final class MailChannel extends LaravelMailChannel
{
    private ?object $notifiable = null;

    public function send($notifiable, Notification $notification)
    {
        $this->notifiable = $notifiable;

        try {
            return parent::send($notifiable, $notification);
        } finally {
            $this->notifiable = null;
        }
    }

    protected function additionalMessageData($notification)
    {
        return [...parent::additionalMessageData($notification), 'foundryGreeting' => Greeting::for($this->notifiable)];
    }
}
