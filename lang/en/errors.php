<?php

return [
    'label' => 'Error :code',
    'home' => 'Go to :name',
    'again' => 'Try again',

    '403' => [
        'title' => 'You can\'t open this page.',
        'lead' => 'It isn\'t public. If someone sent you the link, ask them for a fresh one.',
    ],

    '404' => [
        'title' => 'Nothing stands here.',
        'lead' => 'The address may be mistyped, or the page has moved.',
    ],

    '419' => [
        'title' => 'This page went stale.',
        'lead' => 'It sat open too long. Go back, reload, and try again.',
    ],

    '429' => [
        'title' => 'Too many tries at once.',
        'lead' => 'Wait a minute, then try again.',
    ],

    '500' => [
        'title' => 'Something broke on our side.',
        'lead' => 'Try again in a minute.',
    ],

    '503' => [
        'title' => 'Back in a minute.',
        'lead' => ':name is being updated.',
    ],
];
