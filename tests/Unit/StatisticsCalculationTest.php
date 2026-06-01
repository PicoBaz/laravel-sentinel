<?php

namespace PicoBaz\Sentinel\Tests\Unit;

use PicoBaz\Sentinel\Modules\AIInsights\AIInsightsModule;
use PicoBaz\Sentinel\Modules\SecurityMonitor\SecurityHelper;
use PicoBaz\Sentinel\Tests\TestCase;

/**
 * Unit tests for pure algorithmic methods inside AIInsightsModule.
 * These tests use reflection to access protected methods directly,
 * so no database seeding is needed.
 */
class StatisticsCalculationTest extends TestCase
{
    private AIInsightsModule $module;

    private \ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->module = new AIInsightsModule;
        $this->reflection = new \ReflectionClass($this->module);
    }

    private function callProtected(string $method, array $args = []): mixed
    {
        $m = $this->reflection->getMethod($method);
        $m->setAccessible(true);

        return $m->invoke($this->module, ...$args);
    }

    // ── calculateStatistics ───────────────────────────────────

    public function test_calculate_statistics_with_known_values(): void
    {
        $stats = $this->callProtected('calculateStatistics', [[2, 4, 4, 4, 5, 5, 7, 9]]);

        $this->assertEqualsWithDelta(5.0, $stats['mean'], 0.01);
        $this->assertEqualsWithDelta(2.0, $stats['std_dev'], 0.01);
        $this->assertEquals(2, $stats['min']);
        $this->assertEquals(9, $stats['max']);
    }

    public function test_calculate_statistics_single_value(): void
    {
        $stats = $this->callProtected('calculateStatistics', [[42]]);

        $this->assertEquals(42, $stats['mean']);
        $this->assertEquals(0, $stats['std_dev']);
    }

    public function test_calculate_statistics_empty_array_returns_zeros(): void
    {
        $stats = $this->callProtected('calculateStatistics', [[]]);

        $this->assertEquals(0, $stats['mean']);
        $this->assertEquals(0, $stats['std_dev']);
    }

    public function test_calculate_statistics_identical_values_gives_zero_std_dev(): void
    {
        $stats = $this->callProtected('calculateStatistics', [[5, 5, 5, 5, 5]]);

        $this->assertEquals(5, $stats['mean']);
        $this->assertEquals(0, $stats['std_dev']);
    }

    // ── calculateTrend (Linear Regression) ───────────────────

    public function test_calculate_trend_increasing_sequence(): void
    {
        $slope = $this->callProtected('calculateTrend', [[1, 2, 3, 4, 5]]);

        $this->assertGreaterThan(0, $slope);
        $this->assertEqualsWithDelta(1.0, $slope, 0.01);
    }

    public function test_calculate_trend_decreasing_sequence(): void
    {
        $slope = $this->callProtected('calculateTrend', [[5, 4, 3, 2, 1]]);

        $this->assertLessThan(0, $slope);
        $this->assertEqualsWithDelta(-1.0, $slope, 0.01);
    }

    public function test_calculate_trend_flat_sequence_returns_zero(): void
    {
        $slope = $this->callProtected('calculateTrend', [[10, 10, 10, 10, 10]]);

        $this->assertEqualsWithDelta(0.0, $slope, 0.01);
    }

    public function test_calculate_trend_single_value_returns_zero(): void
    {
        $slope = $this->callProtected('calculateTrend', [[100]]);

        $this->assertEquals(0, $slope);
    }

    public function test_calculate_trend_two_values_returns_correct_slope(): void
    {
        $slope = $this->callProtected('calculateTrend', [[0, 10]]);

        $this->assertGreaterThan(0, $slope);
    }

    // ── calculateConfidence ───────────────────────────────────

    public function test_confidence_low_for_less_than_10_samples(): void
    {
        $confidence = $this->callProtected('calculateConfidence', [5]);
        $this->assertEquals('low', $confidence);
    }

    public function test_confidence_medium_for_10_to_49_samples(): void
    {
        $confidence = $this->callProtected('calculateConfidence', [30]);
        $this->assertEquals('medium', $confidence);
    }

    public function test_confidence_high_for_50_to_99_samples(): void
    {
        $confidence = $this->callProtected('calculateConfidence', [75]);
        $this->assertEquals('high', $confidence);
    }

    public function test_confidence_very_high_for_100_or_more_samples(): void
    {
        $confidence = $this->callProtected('calculateConfidence', [200]);
        $this->assertEquals('very_high', $confidence);
    }

    // ── calculateFrequency ────────────────────────────────────

    public function test_frequency_rare_for_single_item_collection(): void
    {
        $collection = collect([
            (object) ['created_at' => now()->subHour()],
        ]);

        $freq = $this->callProtected('calculateFrequency', [$collection]);

        $this->assertEquals('rare', $freq);
    }

    public function test_frequency_multiple_for_same_hour(): void
    {
        $now = now();
        $collection = collect([
            (object) ['created_at' => $now],
            (object) ['created_at' => $now],
            (object) ['created_at' => $now],
        ]);

        $freq = $this->callProtected('calculateFrequency', [$collection]);

        $this->assertEquals('multiple', $freq);
    }

    // ── Security Score ────────────────────────────────────────

    public function test_threat_level_low_for_score_above_80(): void
    {
        $module = new SecurityHelper;

        $this->assertEquals('low', SecurityHelper::getThreatLevel(100));
        $this->assertEquals('low', SecurityHelper::getThreatLevel(81));
    }

    public function test_threat_level_boundaries(): void
    {
        $this->assertEquals('medium', SecurityHelper::getThreatLevel(79));
        $this->assertEquals('medium', SecurityHelper::getThreatLevel(50));
        $this->assertEquals('high', SecurityHelper::getThreatLevel(49));
        $this->assertEquals('high', SecurityHelper::getThreatLevel(20));
        $this->assertEquals('critical', SecurityHelper::getThreatLevel(19));
        $this->assertEquals('critical', SecurityHelper::getThreatLevel(0));
    }
}
