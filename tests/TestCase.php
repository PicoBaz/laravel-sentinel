<?php

namespace PicoBaz\Sentinel\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use PicoBaz\Sentinel\Providers\SentinelServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    use RefreshDatabase;

    protected function getPackageProviders($app): array
    {
        return [
            SentinelServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {

        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);


        $app['config']->set('cache.default', 'array');


        $app['config']->set('sentinel.enabled', true);
        $app['config']->set('sentinel.thresholds.query_time', 1000);
        $app['config']->set('sentinel.thresholds.memory_usage', 128);
        $app['config']->set('sentinel.thresholds.response_time', 2000);


        $app['config']->set('sentinel.modules.queryMonitor', true);
        $app['config']->set('sentinel.modules.memoryMonitor', true);
        $app['config']->set('sentinel.modules.exceptionMonitor', true);
        $app['config']->set('sentinel.modules.performanceMonitor', true);
        $app['config']->set('sentinel.modules.securityMonitor', true);
        $app['config']->set('sentinel.modules.aiInsights', true);
        $app['config']->set('sentinel.modules.costOptimizer', true);

        $app['config']->set('sentinel.notifications.channels.slack', false);
        $app['config']->set('sentinel.notifications.channels.telegram', false);
        $app['config']->set('sentinel.notifications.channels.email', false);
        $app['config']->set('sentinel.notifications.channels.discord', false);
        $app['config']->set('sentinel.notifications.slack.webhook_url', null);
        $app['config']->set('sentinel.notifications.telegram.bot_token', null);
        $app['config']->set('sentinel.notifications.telegram.chat_id', null);
        $app['config']->set('sentinel.notifications.discord.webhook_url', null);

        $app['config']->set('sentinel.security.enabled', true);
        $app['config']->set('sentinel.security.auto_block', true);
        $app['config']->set('sentinel.security.auto_block_score', 20);
        $app['config']->set('sentinel.security.blacklist', []);

        $app['config']->set('sentinel.cost_optimizer.provider', 'aws');
        $app['config']->set('sentinel.cost_optimizer.instance_type', 't3.small');
        $app['config']->set('sentinel.cost_optimizer.instance_count', 1);
        $app['config']->set('sentinel.cost_optimizer.db_provider', 'aws');
        $app['config']->set('sentinel.cost_optimizer.db_type', 'rds.t3.small');
        $app['config']->set('sentinel.cost_optimizer.storage_provider', 'aws');
        $app['config']->set('sentinel.cost_optimizer.storage_gb', 100);
        $app['config']->set('sentinel.cost_optimizer.cdn_provider', 'aws');
        $app['config']->set('sentinel.cost_optimizer.bandwidth_gb', 500);
        $app['config']->set('sentinel.cost_optimizer.cdn_hit_rate', 70);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../src/Database/Migrations');
    }

    protected function seedPerformanceLogs(int $count = 25, int $baseResponseTime = 800): void
    {
        $records = [];
        for ($i = 0; $i < $count; $i++) {
            $records[] = [
                'type'       => 'performance',
                'data'       => json_encode([
                    'url'           => '/api/products',
                    'method'        => 'GET',
                    'response_time' => $baseResponseTime + ($i * 20),
                    'memory'        => 45 + $i,
                    'status_code'   => 200,
                ]),
                'severity'   => 'info',
                'created_at' => now()->subMinutes($count - $i)->toDateTimeString(),
            ];
        }
        \Illuminate\Support\Facades\DB::table('sentinel_logs')->insert($records);
    }

    protected function seedMemoryLogs(int $count = 25, int $baseUsage = 80): void
    {
        $records = [];
        for ($i = 0; $i < $count; $i++) {
            $records[] = [
                'type'       => 'memory',
                'data'       => json_encode([
                    'usage' => $baseUsage + $i,
                    'peak'  => $baseUsage + $i + 10,
                    'limit' => 128,
                ]),
                'severity'   => $baseUsage + $i > 128 ? 'critical' : 'info',
                'created_at' => now()->subMinutes($count - $i)->toDateTimeString(),
            ];
        }
        \Illuminate\Support\Facades\DB::table('sentinel_logs')->insert($records);
    }

    protected function seedQueryLogs(int $count = 15): void
    {
        $sqls = [
            'SELECT * FROM products WHERE status = ?',
            'SELECT * FROM orders JOIN users ON orders.user_id = users.id',
            'SELECT COUNT(*) FROM orders WHERE created_at > ?',
        ];
        $records = [];
        for ($i = 0; $i < $count; $i++) {
            $time = 300 + ($i * 100);
            $records[] = [
                'type'       => 'query',
                'data'       => json_encode([
                    'sql'      => $sqls[$i % count($sqls)],
                    'time'     => $time,
                    'bindings' => [],
                ]),
                'severity'   => $time > 1000 ? ($time > 3000 ? 'critical' : 'warning') : 'info',
                'created_at' => now()->subMinutes($count - $i)->toDateTimeString(),
            ];
        }
        \Illuminate\Support\Facades\DB::table('sentinel_logs')->insert($records);
    }

    protected function seedExceptionLogs(int $count = 5): void
    {
        $messages = [
            'SQLSTATE[HY000]: General error',
            'Call to undefined method',
            'Class not found',
        ];
        $records = [];
        for ($i = 0; $i < $count; $i++) {
            $records[] = [
                'type'       => 'exception',
                'data'       => json_encode([
                    'message' => $messages[$i % count($messages)],
                    'file'    => '/app/Controllers/TestController.php',
                    'line'    => 100 + $i,
                    'level'   => 'error',
                ]),
                'severity'   => 'critical',
                'created_at' => now()->subMinutes($count - $i)->toDateTimeString(),
            ];
        }
        \Illuminate\Support\Facades\DB::table('sentinel_logs')->insert($records);
    }
}
