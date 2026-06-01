<?php

namespace PicoBaz\Sentinel\Tests\Feature;

use Illuminate\Support\Facades\DB;
use PicoBaz\Sentinel\Facades\Sentinel;
use PicoBaz\Sentinel\Models\SentinelLog;
use PicoBaz\Sentinel\Tests\TestCase;

class PerformanceMonitoringTest extends TestCase
{
    // ── Response Time Logging ─────────────────────────────────

    public function test_performance_log_is_stored_correctly(): void
    {
        $log = Sentinel::log('performance', [
            'url'           => '/api/products',
            'method'        => 'GET',
            'response_time' => 850,
            'memory'        => 45,
            'status_code'   => 200,
        ]);

        $this->assertDatabaseHas('sentinel_logs', ['type' => 'performance']);
        $this->assertEquals('info', $log->severity);
        $this->assertEquals('/api/products', $log->data['url']);
        $this->assertEquals(850, $log->data['response_time']);
    }

    public function test_multiple_performance_logs_are_stored(): void
    {
        $this->seedPerformanceLogs(10);

        $this->assertEquals(10, SentinelLog::where('type', 'performance')->count());
    }

    // ── Query Monitoring ──────────────────────────────────────

    public function test_slow_query_is_logged_as_warning(): void
    {
        $log = Sentinel::log('query', [
            'sql'      => 'SELECT * FROM orders',
            'time'     => 1200,
            'bindings' => [],
        ]);

        $this->assertEquals('warning', $log->severity);
        $this->assertDatabaseHas('sentinel_logs', ['type' => 'query', 'severity' => 'warning']);
    }

    public function test_very_slow_query_is_logged_as_critical(): void
    {
        $log = Sentinel::log('query', [
            'sql'  => 'SELECT * FROM products',
            'time' => 5000,
        ]);

        $this->assertEquals('critical', $log->severity);
    }

    public function test_normal_query_is_logged_as_info(): void
    {
        $log = Sentinel::log('query', [
            'sql'  => 'SELECT id FROM users LIMIT 1',
            'time' => 50,
        ]);

        $this->assertEquals('info', $log->severity);
    }

    public function test_query_log_data_is_persisted_correctly(): void
    {
        $sql = 'SELECT * FROM products WHERE id = ?';
        Sentinel::log('query', [
            'sql'      => $sql,
            'time'     => 200,
            'bindings' => [42],
        ]);

        $log = SentinelLog::where('type', 'query')->first();
        $this->assertEquals($sql, $log->data['sql']);
        $this->assertEquals(200, $log->data['time']);
    }

    // ── Memory Monitoring ─────────────────────────────────────

    public function test_memory_log_exceeding_threshold_is_critical(): void
    {
        // threshold is 128MB, 1.5x = 192
        $log = Sentinel::log('memory', [
            'usage' => 200,
            'peak'  => 210,
            'limit' => 256,
        ]);

        $this->assertEquals('critical', $log->severity);
    }

    public function test_memory_log_above_threshold_is_warning(): void
    {
        $log = Sentinel::log('memory', [
            'usage' => 150,
            'peak'  => 160,
            'limit' => 256,
        ]);

        $this->assertEquals('warning', $log->severity);
    }

    public function test_memory_log_within_threshold_is_info(): void
    {
        $log = Sentinel::log('memory', [
            'usage' => 64,
            'peak'  => 70,
            'limit' => 128,
        ]);

        $this->assertEquals('info', $log->severity);
    }

    // ── Exception Monitoring ──────────────────────────────────

    public function test_exception_log_is_always_critical(): void
    {
        $log = Sentinel::log('exception', [
            'message' => 'Undefined variable',
            'file'    => '/app/Controllers/HomeController.php',
            'line'    => 55,
            'level'   => 'error',
        ]);

        $this->assertEquals('critical', $log->severity);
    }

    public function test_multiple_exception_logs_are_stored(): void
    {
        $this->seedExceptionLogs(5);

        $this->assertEquals(5, SentinelLog::where('type', 'exception')->count());
        $this->assertEquals(5, SentinelLog::where('severity', 'critical')->count());
    }

    // ── Statistics ────────────────────────────────────────────

    public function test_statistics_average_response_time_is_calculated(): void
    {
        foreach ([100, 200, 300, 400] as $time) {
            Sentinel::log('performance', ['url' => '/api/test', 'response_time' => $time]);
        }

        $stats = Sentinel::getStatistics();

        $this->assertIsNumeric($stats['average_response_time']);
    }

    public function test_statistics_slow_queries_count_is_correct(): void
    {
        Sentinel::log('query', ['sql' => 'A', 'time' => 200]);   // info
        Sentinel::log('query', ['sql' => 'B', 'time' => 1500]);  // warning
        Sentinel::log('query', ['sql' => 'C', 'time' => 4000]);  // critical

        $stats = Sentinel::getStatistics();

        $this->assertEquals(2, $stats['slow_queries_count']);
    }

    // ── Data Integrity ────────────────────────────────────────

    public function test_log_data_json_column_is_correctly_decoded(): void
    {
        $data = [
            'url'           => '/api/integrity-test',
            'response_time' => 500,
            'nested'        => ['key' => 'value'],
        ];

        Sentinel::log('performance', $data);

        $log = SentinelLog::where('type', 'performance')->latest('id')->first();

        $this->assertIsArray($log->data);
        $this->assertEquals('/api/integrity-test', $log->data['url']);
        $this->assertEquals('value', $log->data['nested']['key']);
    }

    public function test_bulk_insert_and_query_performance(): void
    {
        $this->seedPerformanceLogs(50);
        $this->seedQueryLogs(20);
        $this->seedMemoryLogs(10);

        $totalLogs = SentinelLog::count();

        $this->assertEquals(80, $totalLogs);
    }
}
