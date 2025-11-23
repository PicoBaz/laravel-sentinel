<?php

return [
    'enabled' => env('SENTINEL_ENABLED', true),

    'modules' => [
        'queryMonitor' => env('SENTINEL_QUERY_MONITOR', true),
        'memoryMonitor' => env('SENTINEL_MEMORY_MONITOR', true),
        'exceptionMonitor' => env('SENTINEL_EXCEPTION_MONITOR', true),
        'performanceMonitor' => env('SENTINEL_PERFORMANCE_MONITOR', true),
    ],

    'thresholds' => [
        'query_time' => env('SENTINEL_QUERY_TIME_THRESHOLD', 1000),
        'memory_usage' => env('SENTINEL_MEMORY_THRESHOLD', 128),
        'response_time' => env('SENTINEL_RESPONSE_TIME_THRESHOLD', 2000),
    ],

    'notifications' => [
        'channels' => [
            'slack' => env('SENTINEL_SLACK_ENABLED', false),
            'telegram' => env('SENTINEL_TELEGRAM_ENABLED', false),
            'email' => env('SENTINEL_EMAIL_ENABLED', true),
            'discord' => env('SENTINEL_DISCORD_ENABLED', false),
        ],

        'slack' => [
            'webhook_url' => env('SENTINEL_SLACK_WEBHOOK'),
        ],

        'telegram' => [
            'bot_token' => env('SENTINEL_TELEGRAM_BOT_TOKEN'),
            'chat_id' => env('SENTINEL_TELEGRAM_CHAT_ID'),
        ],

        'email' => [
            'recipients' => explode(',', env('SENTINEL_EMAIL_RECIPIENTS', '')),
        ],

        'discord' => [
            'webhook_url' => env('SENTINEL_DISCORD_WEBHOOK'),
        ],
    ],

    'storage' => [
        'driver' => env('SENTINEL_STORAGE_DRIVER', 'database'),
        'retention_days' => env('SENTINEL_RETENTION_DAYS', 30),
    ],

    'dashboard' => [
        'enabled' => env('SENTINEL_DASHBOARD_ENABLED', true),
        'route_prefix' => env('SENTINEL_ROUTE_PREFIX', 'sentinel'),
        'middleware' => ['web'],
    ],
];
