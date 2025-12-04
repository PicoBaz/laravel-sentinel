<?php

namespace PicoBaz\Sentinel\Modules\CostOptimizer;

use Illuminate\Support\Facades\Cache;

class CostOptimizerHelper
{
    public static function getCostAnalysis()
    {
        return Cache::get('sentinel:cost:analysis', []);
    }

    public static function getOptimizations()
    {
        return Cache::get('sentinel:cost:optimizations', []);
    }

    public static function getTotalMonthlyCost()
    {
        $analysis = self::getCostAnalysis();
        return $analysis['total_monthly'] ?? 0;
    }

    public static function getTotalYearlyCost()
    {
        return self::getTotalMonthlyCost() * 12;
    }

    public static function getPotentialSavings()
    {
        $optimizations = self::getOptimizations();
        return array_sum(array_column($optimizations, 'savings'));
    }

    public static function getCostBreakdown()
    {
        $analysis = self::getCostAnalysis();
        
        return [
            'compute' => $analysis['compute']['monthly_cost'] ?? 0,
            'database' => $analysis['database']['monthly_cost'] ?? 0,
            'storage' => $analysis['storage']['monthly_cost'] ?? 0,
            'network' => $analysis['network']['monthly_cost'] ?? 0,
            'cache' => $analysis['cache']['monthly_cost'] ?? 0,
        ];
    }

    public static function getCostPerRequest()
    {
        $analysis = self::getCostAnalysis();
        return $analysis['cost_per_request'] ?? 0;
    }

    public static function getEfficiencyScore()
    {
        $analysis = self::getCostAnalysis();
        $optimizations = self::getOptimizations();

        $score = 100;

        if (isset($analysis['compute']['utilization'])) {
            $util = $analysis['compute']['utilization'];
            if ($util < 30) $score -= 20;
            elseif ($util > 80) $score -= 15;
        }

        if (isset($analysis['database']['indexing_score'])) {
            $score -= (100 - $analysis['database']['indexing_score']) * 0.2;
        }

        $highPriorityOpts = collect($optimizations)->where('priority', 'high')->count();
        $score -= min(30, $highPriorityOpts * 10);

        return max(0, min(100, round($score, 2)));
    }

    public static function getEfficiencyGrade()
    {
        $score = self::getEfficiencyScore();

        if ($score >= 90) return 'A';
        if ($score >= 80) return 'B';
        if ($score >= 70) return 'C';
        if ($score >= 60) return 'D';
        return 'F';
    }

    public static function calculateROI($implementationCost = 1000)
    {
        $annualSavings = self::getPotentialSavings() * 12;
        
        if ($implementationCost == 0) {
            return 'Infinite';
        }

        $paybackMonths = $implementationCost / max(1, self::getPotentialSavings());
        $roi = (($annualSavings - $implementationCost) / $implementationCost) * 100;

        return [
            'annual_savings' => round($annualSavings, 2),
            'implementation_cost' => $implementationCost,
            'payback_months' => round($paybackMonths, 1),
            'roi_percent' => round($roi, 2),
            'break_even_date' => now()->addMonths(ceil($paybackMonths))->format('Y-m-d'),
        ];
    }
}
