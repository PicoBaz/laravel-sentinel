<?php

namespace PicoBaz\Sentinel\Tests\Feature;

use Orchestra\Testbench\TestCase;
use PicoBaz\Sentinel\Modules\AIInsights\AIInsightsHelper;
use PicoBaz\Sentinel\Modules\AIInsights\AIInsightsModule;
use PicoBaz\Sentinel\Providers\SentinelServiceProvider;
use Illuminate\Support\Facades\Cache;

class AIInsightsTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [SentinelServiceProvider::class];
    }

    public function test_can_get_health_score()
    {
        $score = AIInsightsHelper::getHealthScore();
        
        $this->assertIsNumeric($score);
        $this->assertGreaterThanOrEqual(0, $score);
        $this->assertLessThanOrEqual(100, $score);
    }

    public function test_can_get_health_status()
    {
        $status = AIInsightsHelper::getHealthStatus();
        
        $validStatuses = ['excellent', 'good', 'fair', 'poor', 'critical'];
        $this->assertContains($status, $validStatuses);
    }

    public function test_can_detect_anomalies()
    {
        $module = new AIInsightsModule();
        
        $anomalies = $module->detectAnomalies();
        
        $this->assertIsArray($anomalies);
        $this->assertArrayHasKey('response_time', $anomalies);
        $this->assertArrayHasKey('memory_usage', $anomalies);
        $this->assertArrayHasKey('error_rate', $anomalies);
    }

    public function test_can_generate_patterns()
    {
        $module = new AIInsightsModule();
        
        $patterns = $module->analyzePatterns();
        
        $this->assertIsArray($patterns);
        $this->assertArrayHasKey('peak_hours', $patterns);
        $this->assertArrayHasKey('slow_endpoints', $patterns);
    }

    public function test_can_generate_predictions()
    {
        $module = new AIInsightsModule();
        
        $predictions = $module->generatePredictions();
        
        $this->assertIsArray($predictions);
    }

    public function test_can_generate_recommendations()
    {
        $module = new AIInsightsModule();
        
        $recommendations = $module->generateRecommendations();
        
        $this->assertIsArray($recommendations);
    }

    public function test_insights_are_cached()
    {
        $module = new AIInsightsModule();
        $module->analyzePatterns();
        
        $this->assertTrue(Cache::has('sentinel:ai:patterns'));
    }

    public function test_can_get_insights_summary()
    {
        $summary = AIInsightsHelper::getInsightsSummary();
        
        $this->assertIsArray($summary);
        $this->assertArrayHasKey('patterns', $summary);
        $this->assertArrayHasKey('anomalies', $summary);
        $this->assertArrayHasKey('predictions', $summary);
        $this->assertArrayHasKey('recommendations', $summary);
    }
}
