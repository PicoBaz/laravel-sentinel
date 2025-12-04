<?php

return [
    'enabled' => env('SENTINEL_ENABLED', true),

    'modules' => [
        'queryMonitor' => env('SENTINEL_QUERY_MONITOR', true),
        'memoryMonitor' => env('SENTINEL_MEMORY_MONITOR', true),
        'exceptionMonitor' => env('SENTINEL_EXCEPTION_MONITOR', true),
        'performanceMonitor' => env('SENTINEL_PERFORMANCE_MONITOR', true),
        'securityMonitor' => env('SENTINEL_SECURITY_MONITOR', true),
        'aiInsights' => env('SENTINEL_AI_INSIGHTS', true),
        'costOptimizer' => env('SENTINEL_COST_OPTIMIZER', true),
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

    'security' => [
        'enabled' => env('SENTINEL_SECURITY_ENABLED', true),
        'auto_block' => env('SENTINEL_SECURITY_AUTO_BLOCK', true),
        'auto_block_score' => env('SENTINEL_SECURITY_AUTO_BLOCK_SCORE', 20),
        'blacklist' => explode(',', env('SENTINEL_SECURITY_BLACKLIST', '')),
        'monitor_failed_logins' => env('SENTINEL_SECURITY_FAILED_LOGINS', true),
        'monitor_suspicious_requests' => env('SENTINEL_SECURITY_SUSPICIOUS_REQUESTS', true),
        'file_integrity_check' => env('SENTINEL_SECURITY_FILE_INTEGRITY', false),
        'protected_files' => [
            base_path('.env'),
            base_path('composer.json'),
            base_path('config/app.php'),
        ],
    ],

    'ai_insights' => [
        'enabled' => env('SENTINEL_AI_INSIGHTS', true),
        'analysis_frequency' => env('SENTINEL_AI_ANALYSIS_FREQUENCY', 'hourly'),
        'prediction_window_hours' => env('SENTINEL_AI_PREDICTION_WINDOW', 24),
        'anomaly_threshold' => env('SENTINEL_AI_ANOMALY_THRESHOLD', 2.5),
        'min_samples_for_prediction' => env('SENTINEL_AI_MIN_SAMPLES', 20),
        'cache_duration_hours' => env('SENTINEL_AI_CACHE_DURATION', 6),
    ],

    'team_collaboration' => [
        'enabled' => env('SENTINEL_TEAM_COLLABORATION', true),
        'auto_assign_critical' => env('SENTINEL_AUTO_ASSIGN_CRITICAL', true),
        'digest_emails' => env('SENTINEL_DIGEST_EMAILS', true),
        'daily_digest_time' => env('SENTINEL_DAILY_DIGEST_TIME', '09:00'),
        'weekly_digest_day' => env('SENTINEL_WEEKLY_DIGEST_DAY', 1),
        'gamification' => [
            'enabled' => env('SENTINEL_GAMIFICATION', true),
            'points' => [
                'issue_assigned' => 5,
                'issue_resolved_low' => 10,
                'issue_resolved_medium' => 20,
                'issue_resolved_high' => 30,
                'issue_resolved_critical' => 50,
                'comment_added' => 2,
            ],
            'badges' => [
                'first_resolver' => 'Resolve your first issue',
                'resolver_10' => 'Resolve 10 issues',
                'resolver_50' => 'Resolve 50 issues',
                'resolver_100' => 'Resolve 100 issues',
                'critical_expert' => 'Resolve 5 critical issues',
                'speed_demon' => 'Resolve 10 issues in under 30 minutes',
            ],
        ],
        'issue_priorities' => ['low', 'medium', 'high', 'critical'],
        'issue_statuses' => ['open', 'in_progress', 'resolved', 'closed'],
    ],

    'cost_optimizer' => [
        'enabled' => env('SENTINEL_COST_OPTIMIZER', true),
        'provider' => env('SENTINEL_COST_PROVIDER', 'aws'),
        'instance_type' => env('SENTINEL_COST_INSTANCE_TYPE', 't3.small'),
        'instance_count' => env('SENTINEL_COST_INSTANCE_COUNT', 1),
        'db_provider' => env('SENTINEL_COST_DB_PROVIDER', 'aws'),
        'db_type' => env('SENTINEL_COST_DB_TYPE', 'rds.t3.small'),
        'storage_provider' => env('SENTINEL_COST_STORAGE_PROVIDER', 'aws'),
        'storage_gb' => env('SENTINEL_COST_STORAGE_GB', 100),
        'cdn_provider' => env('SENTINEL_COST_CDN_PROVIDER', 'aws'),
        'bandwidth_gb' => env('SENTINEL_COST_BANDWIDTH_GB', 500),
        'cdn_hit_rate' => env('SENTINEL_COST_CDN_HIT_RATE', 70),
        'cache_provider' => env('SENTINEL_COST_CACHE_PROVIDER', 'aws'),
        'cache_instance' => env('SENTINEL_COST_CACHE_INSTANCE', 'cache.t3.micro'),
        'analysis_frequency' => env('SENTINEL_COST_ANALYSIS_FREQUENCY', 'daily'),
    ],
];
