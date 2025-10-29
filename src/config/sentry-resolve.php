<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Sentry API Configuration
    |--------------------------------------------------------------------------
    |
    | These are the credentials used to authenticate with the Sentry API.
    | You can get these values from your Sentry.io organization settings.
    |
    */

    'token' => env('SENTRY_TOKEN'),
    'organization' => env('SENTRY_ORG'),
    'project' => env('SENTRY_PROJECT'),

    /*
    |--------------------------------------------------------------------------
    | Default Options
    |--------------------------------------------------------------------------
    |
    | Default options for Sentry commands. These can be overridden when
    | running the commands via command line options.
    |
    */

    'defaults' => [
        'pull' => [
            'limit' => 25,
            'query' => 'is:unresolved',
            'sort' => 'freq',
            'output' => 'SENTRY_TODO.md',
        ],
    ],
];
