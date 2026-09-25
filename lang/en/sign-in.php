<?php

return [
    'title' => 'Sign in',
    'description' => 'Sign in to :name with a link by email. No password needed.',
    'heading' => 'Sign in to :name',
    'lede' => [
        'signup' => "We'll email you a link. No password needed, and if you have no account yet, the link makes one.",
        'members' => "We'll email you a link to your account. No password needed.",
    ],
    'email' => 'Email address',
    'submit' => 'Email me a sign-in link',
    'sent' => [
        'heading' => 'Check your inbox',
        'signup' => 'We sent a sign-in link to :email. It stays valid for :minutes minutes and works once.',
        'members' => 'If :email has an account, a sign-in link is on its way. It stays valid for :minutes minutes and works once.',
        'other' => 'Use a different email address',
    ],
    'confirm' => [
        'title' => 'Signing you in',
        'heading' => 'Signing you in',
        'lede' => 'One moment.',
        'submit' => 'Sign in',
        'switch' => [
            'heading' => 'Switch accounts?',
            'lede' => 'You are signed in as :current. This link signs you in as :email.',
            'submit' => 'Sign in as :email',
        ],
    ],
    'expired' => [
        'heading' => 'This link no longer works',
        'lede' => 'It was used already or has expired. A link works once, for :minutes minutes.',
        'again' => 'Email me a new link',
    ],
    'mail' => [
        'subject' => 'Your sign-in link for :name',
        'intro' => 'Sign in to :name with the button below. The link stays valid for :minutes minutes and works once.',
        'action' => 'Sign in',
        'passkey' => 'Add a passkey in your settings, and next time you sign in with your fingerprint, your face or a security key.',
        'ignore' => "Didn't ask for this? Then you can ignore this email.",
    ],
];
