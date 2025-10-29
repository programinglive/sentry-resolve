<?php

declare(strict_types=1);

$defaultLogPath = function_exists('storage_path')
    ? storage_path('logs')
    : dirname(__DIR__, 2) . '/storage/logs';

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

    'logging' => [
        'enabled' => env('SENTRY_RESOLVE_LOG_ENABLED', true),
        'path' => env('SENTRY_RESOLVE_LOG_PATH', $defaultLogPath),
        'frequency' => env('SENTRY_RESOLVE_LOG_FREQUENCY', 'daily'),
        'prefix' => env('SENTRY_RESOLVE_LOG_PREFIX', 'sentry-resolve'),
    ],
];
