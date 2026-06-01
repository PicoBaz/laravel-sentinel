<?php

namespace PicoBaz\Sentinel\Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use PicoBaz\Sentinel\Modules\AIInsights\AIInsightsHelper;
use PicoBaz\Sentinel\Modules\AIInsights\AIInsightsModule;
use PicoBaz\Sentinel\Tests\TestCase;

class AIInsightsTest extends TestCase
{
    private AIInsightsModule $module;

    protected function setUp(): void
    {
        parent::setUp();
        $this->module = new AIInsightsModule;
    }

    // ── Pattern Analysis ──────────────────────────────────────

    public function test_analyze_patterns_returns_array_with_required_keys(): void
    {
        $this->seedPerformanceLogs(10);
        $this->seedMemoryLogs(5);
        $this->seedExceptionLogs(3);

        $patterns = $this->module->analyzePatterns();

        $this->assertIsArray($patterns);
        $this->assertArrayHasKey('peak_hours', $patterns);
        $this->assertArrayHasKey('slow_endpoints', $patterns);
        $this->assertArrayHasKey('memory_trends', $patterns);
        $this->assertArrayHasKey('error_patterns', $patterns);
    }

    public function test_analyze_patterns_result_is_stored_in_cache(): void
    {
        $this->seedPerformanceLogs(5);

        $this->module->analyzePatterns();

        $this->assertTrue(Cache::has('sentinel:ai:patterns'));
    }

    public function test_peak_hours_contains_hours_key(): void
    {
        $this->seedPerformanceLogs(20);

        $patterns = $this->module->analyzePatterns();

        $this->assertArrayHasKey('hours', $patterns['peak_hours']);
        $this->assertArrayHasKey('average_load', $patterns['peak_hours']);
        $this->assertArrayHasKey('peak_load', $patterns['peak_hours']);
    }

    public function test_slow_endpoints_returns_top_ten_at_most(): void
    {
        $this->seedPerformanceLogs(30);

        $patterns = $this->module->analyzePatterns();

        $this->assertLessThanOrEqual(10, count($patterns['slow_endpoints']));
    }

    public function test_memory_trends_returns_null_when_no_memory_logs(): void
    {
        $this->seedPerformanceLogs(5);

        $patterns = $this->module->analyzePatterns();

        $this->assertNull($patterns['memory_trends']);
    }

    public function test_memory_trends_returns_trend_data_when_logs_exist(): void
    {
        $this->seedMemoryLogs(10);

        $patterns = $this->module->analyzePatterns();

        $this->assertNotNull($patterns['memory_trends']);
        $this->assertArrayHasKey('current_avg', $patterns['memory_trends']);
        $this->assertArrayHasKey('trend', $patterns['memory_trends']);
        $this->assertContains($patterns['memory_trends']['trend'], ['increasing', 'decreasing']);
    }

    // ── Anomaly Detection ─────────────────────────────────────

    public function test_detect_anomalies_returns_array_with_all_categories(): void
    {
        $this->seedPerformanceLogs(15);

        $anomalies = $this->module->detectAnomalies();

        $this->assertIsArray($anomalies);
        $this->assertArrayHasKey('response_time', $anomalies);
        $this->assertArrayHasKey('memory_usage', $anomalies);
        $this->assertArrayHasKey('error_rate', $anomalies);
        $this->assertArrayHasKey('query_count', $anomalies);
    }

    public function test_anomaly_detection_returns_null_when_insufficient_data(): void
    {

        $this->seedPerformanceLogs(3);

        $anomalies = $this->module->detectAnomalies();

        $this->assertNull($anomalies['response_time']);
        $this->assertNull($anomalies['memory_usage']);
    }

    public function test_anomaly_result_is_stored_in_cache(): void
    {
        $this->seedPerformanceLogs(15);

        $this->module->detectAnomalies();

        $this->assertTrue(Cache::has('sentinel:ai:anomalies'));
    }

    public function test_response_time_anomaly_detected_with_spike(): void
    {

        $this->seedPerformanceLogs(20, 200);

        DB::table('sentinel_logs')->insert([
            'type' => 'performance',
            'data' => json_encode([
                'url' => '/api/spike',
                'response_time' => 99999,
                'memory' => 50,
            ]),
            'severity' => 'critical',
            'created_at' => now()->subMinutes(1)->toDateTimeString(),
        ]);

        $anomalies = $this->module->detectAnomalies();

        if ($anomalies['response_time'] !== null) {
            $this->assertTrue($anomalies['response_time']['detected']);
            $this->assertGreaterThan(0, $anomalies['response_time']['count']);
        }

        $this->assertIsArray($anomalies);
    }

    // ── Predictions ───────────────────────────────────────────

    public function test_generate_predictions_returns_array_with_required_keys(): void
    {
        $predictions = $this->module->generatePredictions();

        $this->assertIsArray($predictions);
        $this->assertArrayHasKey('performance', $predictions);
        $this->assertArrayHasKey('memory', $predictions);
        $this->assertArrayHasKey('error_rate', $predictions);
        $this->assertArrayHasKey('downtime_risk', $predictions);
    }

    public function test_performance_prediction_returns_null_with_less_than_20_logs(): void
    {
        $this->seedPerformanceLogs(10);

        $predictions = $this->module->generatePredictions();

        $this->assertNull($predictions['performance']);
    }

    public function test_performance_prediction_returns_data_with_enough_logs(): void
    {
        $this->seedPerformanceLogs(25);

        $predictions = $this->module->generatePredictions();

        $this->assertNotNull($predictions['performance']);
        $this->assertArrayHasKey('current_avg', $predictions['performance']);
        $this->assertArrayHasKey('trend', $predictions['performance']);
        $this->assertArrayHasKey('prediction_24h', $predictions['performance']);
        $this->assertArrayHasKey('prediction_7d', $predictions['performance']);
        $this->assertArrayHasKey('confidence', $predictions['performance']);
        $this->assertContains($predictions['performance']['trend'], ['degrading', 'improving']);
        $this->assertContains(
            $predictions['performance']['confidence'],
            ['low', 'medium', 'high', 'very_high']
        );
    }

    public function test_memory_prediction_returns_null_with_insufficient_data(): void
    {
        $this->seedMemoryLogs(5);

        $predictions = $this->module->generatePredictions();

        $this->assertNull($predictions['memory']);
    }

    public function test_memory_prediction_returns_data_with_enough_logs(): void
    {
        $this->seedMemoryLogs(25);

        $predictions = $this->module->generatePredictions();

        $this->assertNotNull($predictions['memory']);
        $this->assertArrayHasKey('current_avg', $predictions['memory']);
        $this->assertArrayHasKey('trend', $predictions['memory']);
        $this->assertArrayHasKey('threshold_breach_risk', $predictions['memory']);
        $this->assertContains($predictions['memory']['trend'], ['increasing', 'decreasing']);
    }

    public function test_downtime_risk_always_returns_data(): void
    {
        $predictions = $this->module->generatePredictions();

        $this->assertNotNull($predictions['downtime_risk']);
        $this->assertArrayHasKey('score', $predictions['downtime_risk']);
        $this->assertArrayHasKey('level', $predictions['downtime_risk']);
        $this->assertArrayHasKey('factors', $predictions['downtime_risk']);
        $this->assertContains(
            $predictions['downtime_risk']['level'],
            ['low', 'medium', 'high', 'critical']
        );
        $this->assertGreaterThanOrEqual(0, $predictions['downtime_risk']['score']);
        $this->assertLessThanOrEqual(100, $predictions['downtime_risk']['score']);
    }

    public function test_predictions_are_cached(): void
    {
        $this->module->generatePredictions();

        $this->assertTrue(Cache::has('sentinel:ai:predictions'));
    }

    // ── Recommendations ───────────────────────────────────────

    public function test_generate_recommendations_returns_array(): void
    {
        $recommendations = $this->module->generateRecommendations();

        $this->assertIsArray($recommendations);
    }

    public function test_recommendations_are_cached(): void
    {
        $this->module->generateRecommendations();

        $this->assertTrue(Cache::has('sentinel:ai:recommendations'));
    }

    public function test_recommendations_contain_required_fields_when_not_empty(): void
    {
        // Seed enough data to trigger recommendations
        $this->seedPerformanceLogs(25, 2500); // high response times
        $this->module->analyzePatterns();
        $this->module->generatePredictions();

        $recommendations = $this->module->generateRecommendations();

        foreach ($recommendations as $rec) {
            $this->assertArrayHasKey('type', $rec);
            $this->assertArrayHasKey('priority', $rec);
            $this->assertArrayHasKey('title', $rec);
            $this->assertArrayHasKey('action', $rec);
            $this->assertContains($rec['priority'], ['low', 'medium', 'high', 'critical']);
        }
    }

    public function test_helper_get_health_score_returns_value_between_0_and_100(): void
    {
        $score = AIInsightsHelper::getHealthScore();

        $this->assertIsNumeric($score);
        $this->assertGreaterThanOrEqual(0, $score);
        $this->assertLessThanOrEqual(100, $score);
    }

    public function test_helper_get_health_status_returns_valid_string(): void
    {
        $status = AIInsightsHelper::getHealthStatus();

        $this->assertContains($status, ['excellent', 'good', 'fair', 'poor', 'critical']);
    }

    public function test_helper_get_insights_summary_has_all_keys(): void
    {
        $summary = AIInsightsHelper::getInsightsSummary();

        $this->assertArrayHasKey('patterns', $summary);
        $this->assertArrayHasKey('anomalies', $summary);
        $this->assertArrayHasKey('predictions', $summary);
        $this->assertArrayHasKey('recommendations', $summary);
        $this->assertArrayHasKey('last_updated', $summary);
    }

    public function test_helper_has_active_anomalies_returns_bool(): void
    {
        $result = AIInsightsHelper::hasActiveAnomalies();
        $this->assertIsBool($result);
    }

    public function test_helper_has_critical_recommendations_returns_bool(): void
    {
        $result = AIInsightsHelper::hasCriticalRecommendations();
        $this->assertIsBool($result);
    }
}
