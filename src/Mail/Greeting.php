<?php

namespace Steddle\Foundry\Mail;

use Illuminate\Support\Str;

/**
 * The line a notification opens on where it states none: the recipient by
 * the first word of their name, or no name where they have not given one,
 * their name being empty or the local part of their address. A name the
 * recipient confirmed on /welcome is theirs, whatever their address says.
 */
final class Greeting
{
    public static function for(?object $notifiable): string
    {
        $name = trim((string) data_get($notifiable, 'name'));

        $unnamed = match (true) {
            $name === '' => true,
            method_exists($notifiable, 'hasOnboarded') && $notifiable->hasOnboarded() => false,
            method_exists($notifiable, 'hasPlaceholderName') => $notifiable->hasPlaceholderName(),
            default => Str::lower($name) === Str::lower(Str::before((string) data_get($notifiable, 'email'), '@')),
        };

        return $unnamed
            ? __('foundry::mail.greeting_unnamed')
            : __('foundry::mail.greeting', ['name' => Str::before($name, ' ')]);
    }
}
