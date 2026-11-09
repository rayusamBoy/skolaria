<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Toggleable Feature Modules
    |--------------------------------------------------------------------------
    | Only modules listed here can be turned off by tenant admins. Each entry
    | maps a module key to the permission prefix(es) it owns. When a module
    | is disabled, every permission starting with one of its prefixes is
    | denied system‑wide (trait, Blade, Gate, Spatie – all of it).
    */
    'toggleable' => [
        'analytics' => [
            'label' => 'Analytics',
            'description' => 'Google Analytics integration and reports.',
            'permission_prefixes' => ['analytics'],
            'icon' => 'analytics',
        ],
        'messages' => [
            'label' => 'Messages',
            'description' => 'Internal messaging between users.',
            'permission_prefixes' => ['message_threads'],
            'icon' => 'chat',
        ],
        'tickets' => [
            'label' => 'Support Tickets',
            'description' => 'Internal support ticket system.',
            'permission_prefixes' => ['tickets'],
            'icon' => 'support',
        ],
        'query_builders' => [
            'label' => 'Query Builder',
            'description' => 'Advanced data querying tool.',
            'permission_prefixes' => ['query_builders'],
            'icon' => 'join',
        ],
        'schedules' => [
            'label' => 'Calendar Events',
            'description' => 'School calendar and event management.',
            'permission_prefixes' => ['schedules'],
            'icon' => 'calendar_month',
        ],
    ],

    // Cache TTL (in seconds) for the module state lookups
    'cache_ttl' => 60 * 60, // 1 hour
];
