<?php

return [
    'title' => 'Inloggen',
    'description' => 'Log in bij :name met een link per mail. Geen wachtwoord nodig.',
    'heading' => 'Inloggen bij :name',
    'lede' => [
        'signup' => 'We mailen je een link. Geen wachtwoord nodig, en heb je nog geen account, dan maakt die link er een aan.',
        'members' => 'We mailen je een link naar je account. Geen wachtwoord nodig.',
    ],
    'email' => 'E-mailadres',
    'submit' => 'Mail mij een inloglink',
    'sent' => [
        'heading' => 'Kijk in je inbox',
        'signup' => 'We hebben een inloglink gestuurd naar :email. Hij is :minutes minuten geldig en werkt één keer.',
        'members' => 'Heeft :email een account, dan is er een inloglink onderweg. Hij is :minutes minuten geldig en werkt één keer.',
        'other' => 'Een ander e-mailadres gebruiken',
    ],
    'confirm' => [
        'title' => 'Je wordt ingelogd',
        'heading' => 'Je wordt ingelogd',
        'lede' => 'Een ogenblik.',
        'submit' => 'Inloggen',
        'switch' => [
            'heading' => 'Van account wisselen?',
            'lede' => 'Je bent ingelogd als :current. Deze link logt je in als :email.',
            'submit' => 'Inloggen als :email',
        ],
    ],
    'expired' => [
        'heading' => 'Deze link werkt niet meer',
        'lede' => 'Hij is al gebruikt of verlopen. Een link werkt één keer, :minutes minuten lang.',
        'again' => 'Mail mij een nieuwe link',
    ],
    'mail' => [
        'subject' => 'Je inloglink voor :name',
        'intro' => 'Log in bij :name met de knop hieronder. De link is :minutes minuten geldig en werkt één keer.',
        'action' => 'Inloggen',
        'ignore' => 'Niet om gevraagd? Dan kun je deze mail negeren.',
    ],
];
