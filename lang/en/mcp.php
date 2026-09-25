<?php

return [
    'consent' => [
        'title' => 'Connect :client',
        'description' => ':client asks to connect to :name as you.',
        'eyebrow' => 'Agent access',
        'heading' => 'Connect :client to :name',
        'lede' => ':client asks to connect to :name as you, and receives its access at :hosts. Allow it only if you started this connection yourself.',
        'may' => 'It may',
        'cancel' => 'Cancel',
        'approve' => 'Allow access',
    ],
    'agents' => [
        'title' => 'Agents',
        'lead' => 'Connect Claude or another MCP client to :name. It asks you to sign in and allow its access.',
        'url' => 'Server URL',
        'command' => 'Claude Code',
        'connected' => 'Connected',
        'none' => 'No agents connected yet.',
        'since' => 'Connected :time',
        'disconnect' => 'Disconnect',
    ],
];
