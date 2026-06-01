<?php

namespace PicoBaz\Sentinel\Tests\Feature;

use Illuminate\Support\Facades\Event;
use PicoBaz\Sentinel\Events\AlertTriggered;
use PicoBaz\Sentinel\Facades\Sentinel;
use PicoBaz\Sentinel\Models\SentinelLog;
use PicoBaz\Sentinel\Tests\TestCase;

class SentinelServiceTest extends TestCase
{
    public function test_log_creates_record_in_database(): void
    {
        $log = Sentinel::log('performance', [
            'url'           => '/api/test',
            'response_time' => 500,
            'memory'        => 45,
        ]);

        $this->assertNotNull($log);
        $this->assertInstanceOf(SentinelLog::class, $log);
        $this->assertDatabaseHas('sentinel_logs', [
            'type'     => 'performance',
            'severity' => 'info',
        ]);
    }

    public function test_log_returns_null_gracefully_when_exception_occurs(): void
    {
        \Illuminate\Support\Facades\Schema::drop('sentinel_logs');
        \Illuminate\Support\Facades\Schema::drop('sentinel_security_blacklist');

        $result = Sentinel::log('performance', ['url' => '/test']);
        $this->assertNull($result);

        $this->artisan('migrate');
    }

    public function test_exception_log_is_marked_as_critical(): void
    {
        $log = Sentinel::log('exception', [
            'message' => 'Something went wrong',
            'file'    => '/app/Test.php',
            'line'    => 42,
        ]);

        $this->assertEquals('critical', $log->severity);
    }

    public function test_slow_query_log_is_marked_as_warning(): void
    {
        $log = Sentinel::log('query', [
            'sql'  => 'SELECT * FROM users',
            'time' => 1500,
        ]);

        $this->assertEquals('warning', $log->severity);
    }

    public function test_very_slow_query_log_is_marked_as_critical(): void
    {
        $log = Sentinel::log('query', [
            'sql'  => 'SELECT * FROM orders',
            'time' => 4000,
        ]);

        $this->assertEquals('critical', $log->severity);
    }

    public function test_high_memory_usage_log_is_marked_as_critical(): void
    {
        $log = Sentinel::log('memory', [
            'usage' => 200,
            'peak'  => 210,
            'limit' => 256,
        ]);

        $this->assertEquals('critical', $log->severity);
    }

    public function test_alert_event_is_fired_for_critical_log(): void
    {
        Event::fake([AlertTriggered::class]);

        Sentinel::log('exception', [
            'message' => 'Critical error occurred',
        ]);

        Event::assertDispatched(AlertTriggered::class, function ($event) {
            return $event->log->severity === 'critical';
        });
    }

    public function test_alert_event_is_not_fired_for_info_log(): void
    {
        Event::fake([AlertTriggered::class]);

        Sentinel::log('performance', [
            'url'           => '/api/fast',
            'response_time' => 100,
        ]);

        Event::assertNotDispatched(AlertTriggered::class);
    }

    public function test_get_metrics_returns_collection(): void
    {
        $this->seedPerformanceLogs(5);

        $metrics = Sentinel::getMetrics('performance', 24);

        $this->assertCount(5, $metrics);
    }

    public function test_get_metrics_returns_empty_collection_on_error(): void
    {
        \Illuminate\Support\Facades\Schema::drop('sentinel_logs');
        \Illuminate\Support\Facades\Schema::drop('sentinel_security_blacklist');

        $metrics = Sentinel::getMetrics('performance');
        $this->assertCount(0, $metrics);

        $this->artisan('migrate');
    }

    public function test_get_statistics_returns_correct_counts(): void
    {
        $this->seedPerformanceLogs(3);
        $this->seedExceptionLogs(2);

        $stats = Sentinel::getStatistics();

        $this->assertEquals(5, $stats['total_logs']);
        $this->assertEquals(2, $stats['critical_logs']);
    }

    public function test_get_statistics_returns_zeros_when_table_missing(): void
    {
        \Illuminate\Support\Facades\Schema::drop('sentinel_logs');
        \Illuminate\Support\Facades\Schema::drop('sentinel_security_blacklist');

        $stats = Sentinel::getStatistics();

        $this->assertEquals(0, $stats['total_logs']);
        $this->assertEquals(0, $stats['critical_logs']);

        $this->artisan('migrate');
    }

    public function test_sentinel_log_data_is_cast_to_array(): void
    {
        $log = Sentinel::log('performance', [
            'url'           => '/api/cast-test',
            'response_time' => 300,
        ]);

        $fresh = SentinelLog::find($log->id);

        $this->assertIsArray($fresh->data);
        $this->assertEquals('/api/cast-test', $fresh->data['url']);
    }
}
