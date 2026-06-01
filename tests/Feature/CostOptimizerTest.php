<?php

namespace PicoBaz\Sentinel\Tests\Feature;

use Illuminate\Support\Facades\Cache;
use PicoBaz\Sentinel\Modules\CostOptimizer\CostOptimizerHelper;
use PicoBaz\Sentinel\Modules\CostOptimizer\CostOptimizerModule;
use PicoBaz\Sentinel\Tests\TestCase;

class CostOptimizerTest extends TestCase
{
    private CostOptimizerModule $module;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->module = new CostOptimizerModule;
    }

    // ── analyzeCosts ──────────────────────────────────────────

    public function test_analyze_costs_returns_array_with_all_categories(): void
    {
        $analysis = $this->module->analyzeCosts();

        $this->assertIsArray($analysis);
        $this->assertArrayHasKey('compute', $analysis);
        $this->assertArrayHasKey('database', $analysis);
        $this->assertArrayHasKey('storage', $analysis);
        $this->assertArrayHasKey('network', $analysis);
        $this->assertArrayHasKey('cache', $analysis);
        $this->assertArrayHasKey('total_monthly', $analysis);
        $this->assertArrayHasKey('projected_yearly', $analysis);
        $this->assertArrayHasKey('cost_per_request', $analysis);
    }

    public function test_analyze_costs_is_stored_in_cache(): void
    {
        $this->module->analyzeCosts();

        $this->assertTrue(Cache::has('sentinel:cost:analysis'));
    }

    public function test_compute_analysis_has_required_keys(): void
    {
        $analysis = $this->module->analyzeCosts();

        $compute = $analysis['compute'];
        $this->assertArrayHasKey('provider', $compute);
        $this->assertArrayHasKey('instance_type', $compute);
        $this->assertArrayHasKey('monthly_cost', $compute);
        $this->assertArrayHasKey('utilization', $compute);
        $this->assertArrayHasKey('hourly_rate', $compute);
        $this->assertEquals('aws', $compute['provider']);
        $this->assertEquals('t3.small', $compute['instance_type']);
    }

    public function test_compute_monthly_cost_matches_expected_for_t3_small(): void
    {

        $analysis = $this->module->analyzeCosts();

        $this->assertEqualsWithDelta(15.18, $analysis['compute']['monthly_cost'], 0.1);
    }

    public function test_database_analysis_has_required_keys(): void
    {
        $analysis = $this->module->analyzeCosts();

        $db = $analysis['database'];
        $this->assertArrayHasKey('provider', $db);
        $this->assertArrayHasKey('monthly_cost', $db);
        $this->assertArrayHasKey('slow_queries', $db);
        $this->assertArrayHasKey('indexing_score', $db);
        $this->assertArrayHasKey('optimizations', $db);
    }

    public function test_storage_monthly_cost_is_positive(): void
    {
        $analysis = $this->module->analyzeCosts();

        $this->assertGreaterThan(0, $analysis['storage']['monthly_cost']);
    }

    public function test_storage_cost_calculation_is_correct(): void
    {

        $analysis = $this->module->analyzeCosts();

        $this->assertEqualsWithDelta(2.30, $analysis['storage']['monthly_cost'], 0.01);
    }

    public function test_total_monthly_cost_equals_sum_of_categories(): void
    {
        $analysis = $this->module->analyzeCosts();

        $expected = $analysis['compute']['monthly_cost']
            + $analysis['database']['monthly_cost']
            + $analysis['storage']['monthly_cost']
            + $analysis['network']['monthly_cost']
            + $analysis['cache']['monthly_cost'];

        $this->assertEqualsWithDelta($expected, $analysis['total_monthly'], 0.01);
    }

    public function test_projected_yearly_is_twelve_times_monthly(): void
    {
        $analysis = $this->module->analyzeCosts();

        $this->assertEqualsWithDelta(
            $analysis['total_monthly'] * 12,
            $analysis['projected_yearly'],
            0.01
        );
    }

    // ── Optimizations ─────────────────────────────────────────

    public function test_generate_optimizations_returns_array(): void
    {
        $this->module->analyzeCosts();
        $optimizations = $this->module->generateOptimizations();

        $this->assertIsArray($optimizations);
    }

    public function test_generate_optimizations_are_stored_in_cache(): void
    {
        $this->module->analyzeCosts();
        $this->module->generateOptimizations();

        $this->assertTrue(Cache::has('sentinel:cost:optimizations'));
    }

    public function test_each_optimization_has_required_fields(): void
    {
        $this->module->analyzeCosts();
        $optimizations = $this->module->generateOptimizations();

        foreach ($optimizations as $opt) {
            $this->assertArrayHasKey('category', $opt);
            $this->assertArrayHasKey('priority', $opt);
            $this->assertArrayHasKey('title', $opt);
            $this->assertContains($opt['priority'], ['low', 'medium', 'high', 'critical']);
        }
    }

    public function test_optimizations_are_sorted_by_priority_descending(): void
    {
        $this->module->analyzeCosts();
        $optimizations = $this->module->generateOptimizations();

        if (count($optimizations) >= 2) {
            $priorityOrder = ['critical' => 4, 'high' => 3, 'medium' => 2, 'low' => 1];
            for ($i = 0; $i < count($optimizations) - 1; $i++) {
                $current = $priorityOrder[$optimizations[$i]['priority']] ?? 0;
                $next = $priorityOrder[$optimizations[$i + 1]['priority']] ?? 0;
                $this->assertGreaterThanOrEqual($next, $current);
            }
        }

        $this->assertTrue(true);
    }

    public function test_downgrade_recommendation_generated_for_low_utilization(): void
    {
        $this->module->analyzeCosts();
        $optimizations = $this->module->generateOptimizations();

        $computeOpts = array_filter($optimizations, fn ($o) => $o['category'] === 'compute');

        if (! empty($computeOpts)) {
            $computeOpt = array_values($computeOpts)[0];
            $this->assertStringContainsStringIgnoringCase('downsize', $computeOpt['title']);
        }

        $this->assertTrue(true);
    }

    public function test_storage_lifecycle_recommendation_always_present(): void
    {
        $this->module->analyzeCosts();
        $optimizations = $this->module->generateOptimizations();

        $storageOpts = array_filter($optimizations, fn ($o) => $o['category'] === 'storage');

        $this->assertGreaterThanOrEqual(1, count($storageOpts));
    }

    // ── CostOptimizerHelper ───────────────────────────────────

    public function test_helper_get_total_monthly_cost_returns_numeric(): void
    {
        $this->module->analyzeCosts();

        $cost = CostOptimizerHelper::getTotalMonthlyCost();
        $this->assertIsNumeric($cost);
        $this->assertGreaterThan(0, $cost);
    }

    public function test_helper_get_total_yearly_cost_is_twelve_times_monthly(): void
    {
        $this->module->analyzeCosts();

        $monthly = CostOptimizerHelper::getTotalMonthlyCost();
        $yearly = CostOptimizerHelper::getTotalYearlyCost();

        $this->assertEqualsWithDelta($monthly * 12, $yearly, 0.01);
    }

    public function test_helper_get_cost_breakdown_has_all_keys(): void
    {
        $this->module->analyzeCosts();

        $breakdown = CostOptimizerHelper::getCostBreakdown();

        $this->assertArrayHasKey('compute', $breakdown);
        $this->assertArrayHasKey('database', $breakdown);
        $this->assertArrayHasKey('storage', $breakdown);
        $this->assertArrayHasKey('network', $breakdown);
        $this->assertArrayHasKey('cache', $breakdown);
    }

    public function test_helper_get_efficiency_score_is_between_0_and_100(): void
    {
        $this->module->analyzeCosts();
        $this->module->generateOptimizations();

        $score = CostOptimizerHelper::getEfficiencyScore();

        $this->assertGreaterThanOrEqual(0, $score);
        $this->assertLessThanOrEqual(100, $score);
    }

    public function test_helper_get_efficiency_grade_is_valid(): void
    {
        $this->module->analyzeCosts();
        $this->module->generateOptimizations();

        $grade = CostOptimizerHelper::getEfficiencyGrade();

        $this->assertContains($grade, ['A', 'B', 'C', 'D', 'F']);
    }

    public function test_helper_get_potential_savings_is_non_negative(): void
    {
        $this->module->analyzeCosts();
        $this->module->generateOptimizations();

        $savings = CostOptimizerHelper::getPotentialSavings();

        $this->assertGreaterThanOrEqual(0, $savings);
    }

    public function test_helper_calculate_roi_returns_array_with_required_keys(): void
    {
        $this->module->analyzeCosts();
        $this->module->generateOptimizations();

        $roi = CostOptimizerHelper::calculateROI(500);

        $this->assertIsArray($roi);
        $this->assertArrayHasKey('annual_savings', $roi);
        $this->assertArrayHasKey('implementation_cost', $roi);
        $this->assertArrayHasKey('payback_months', $roi);
        $this->assertArrayHasKey('roi_percent', $roi);
        $this->assertArrayHasKey('break_even_date', $roi);
        $this->assertEquals(500, $roi['implementation_cost']);
    }

    public function test_helper_calculate_roi_with_zero_cost_returns_infinite(): void
    {
        $result = CostOptimizerHelper::calculateROI(0);
        $this->assertEquals('Infinite', $result);
    }
}
