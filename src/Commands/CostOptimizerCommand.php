<?php

namespace PicoBaz\Sentinel\Commands;

use Illuminate\Console\Command;
use PicoBaz\Sentinel\Modules\CostOptimizer\CostOptimizerHelper;
use PicoBaz\Sentinel\Modules\CostOptimizer\CostOptimizerModule;

class CostOptimizerCommand extends Command
{
    protected $signature = 'sentinel:cost-optimizer {--refresh}';
    protected $description = 'Analyze infrastructure costs and get optimization recommendations';

    public function handle()
    {
        if ($this->option('refresh')) {
            $this->info('Analyzing infrastructure costs...');
            $module = app(CostOptimizerModule::class);
            $module->analyzeCosts();
            $module->generateOptimizations();
            $this->info('Analysis complete!');
            $this->line('');
        }

        $this->displayCostOverview();
        $this->line('');
        
        $this->displayCostBreakdown();
        $this->line('');
        
        $this->displayEfficiency();
        $this->line('');
        
        $this->displayOptimizations();
        $this->line('');
        
        $this->displayROI();
    }

    protected function displayCostOverview()
    {
        $monthly = CostOptimizerHelper::getTotalMonthlyCost();
        $yearly = CostOptimizerHelper::getTotalYearlyCost();
        $perRequest = CostOptimizerHelper::getCostPerRequest();

        $this->info('💰 Cost Overview');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Monthly Cost', '$' . number_format($monthly, 2)],
                ['Yearly Cost', '$' . number_format($yearly, 2)],
                ['Cost per 1K Requests', '$' . number_format($perRequest, 4)],
            ]
        );
    }

    protected function displayCostBreakdown()
    {
        $breakdown = CostOptimizerHelper::getCostBreakdown();
        $total = array_sum($breakdown);

        $this->info('📊 Cost Breakdown');
        
        $data = [];
        foreach ($breakdown as $category => $cost) {
            $percentage = $total > 0 ? ($cost / $total) * 100 : 0;
            $data[] = [
                ucfirst($category),
                '$' . number_format($cost, 2),
                round($percentage, 1) . '%',
                $this->getProgressBar($percentage),
            ];
        }

        $this->table(['Category', 'Monthly Cost', 'Share', 'Distribution'], $data);
    }

    protected function displayEfficiency()
    {
        $score = CostOptimizerHelper::getEfficiencyScore();
        $grade = CostOptimizerHelper::getEfficiencyGrade();

        $this->info('⚡ Efficiency Score');
        
        $color = match($grade) {
            'A', 'B' => 'info',
            'C' => 'comment',
            default => 'error',
        };

        $this->{$color}("Grade: {$grade} | Score: {$score}/100");
        
        if ($grade === 'A') {
            $this->info('Excellent! Your infrastructure is well optimized.');
        } elseif ($grade === 'B') {
            $this->comment('Good! Some minor optimizations available.');
        } elseif ($grade === 'C') {
            $this->warn('Fair. Consider implementing suggested optimizations.');
        } else {
            $this->error('Poor. Significant optimization opportunities available!');
        }
    }

    protected function displayOptimizations()
    {
        $optimizations = CostOptimizerHelper::getOptimizations();

        $this->info('💡 Optimization Recommendations');

        if (empty($optimizations)) {
            $this->info('No optimizations needed at this time! ✓');
            return;
        }

        foreach ($optimizations as $opt) {
            $priorityColor = match($opt['priority']) {
                'critical' => 'error',
                'high' => 'warn',
                'medium' => 'comment',
                default => 'info',
            };

            $emoji = match($opt['priority']) {
                'critical' => '🚨',
                'high' => '⚠️',
                'medium' => '📌',
                default => 'ℹ️',
            };

            $this->{$priorityColor}("  {$emoji} [{$opt['priority']}] {$opt['title']}");
            $this->line("    Category: {$opt['category']}");
            
            if (isset($opt['savings']) && $opt['savings'] > 0) {
                $this->line("    💰 Savings: $" . number_format($opt['savings'], 2) . "/month");
            }

            if (isset($opt['performance_gain'])) {
                $this->line("    ⚡ Performance Gain: {$opt['performance_gain']}");
            }

            if (isset($opt['action'])) {
                $this->comment("    → {$opt['action']}");
            }

            $this->line("    ⏱️  Implementation: {$opt['implementation_time']} | Risk: {$opt['risk']}");
            $this->line('');
        }

        $totalSavings = array_sum(array_column($optimizations, 'savings'));
        if ($totalSavings > 0) {
            $this->info("💵 Total Potential Savings: $" . number_format($totalSavings, 2) . "/month ($" . number_format($totalSavings * 12, 2) . "/year)");
        }
    }

    protected function displayROI()
    {
        $savings = CostOptimizerHelper::getPotentialSavings();

        if ($savings == 0) {
            return;
        }

        $this->info('📈 Return on Investment');

        $implementationCosts = [
            ['Scenario', 'Cost', 'Payback', 'Annual ROI'],
            ['DIY Implementation', '$500', $this->calculatePayback(500, $savings), $this->calculateROI(500, $savings)],
            ['Contractor (Small)', '$2,000', $this->calculatePayback(2000, $savings), $this->calculateROI(2000, $savings)],
            ['Contractor (Medium)', '$5,000', $this->calculatePayback(5000, $savings), $this->calculateROI(5000, $savings)],
        ];

        $this->table($implementationCosts[0], array_slice($implementationCosts, 1));
    }

    protected function getProgressBar($percentage)
    {
        $bars = round($percentage / 10);
        return str_repeat('█', $bars) . str_repeat('░', 10 - $bars);
    }

    protected function calculatePayback($cost, $monthlySavings)
    {
        if ($monthlySavings == 0) return 'N/A';
        $months = $cost / $monthlySavings;
        return round($months, 1) . ' months';
    }

    protected function calculateROI($cost, $monthlySavings)
    {
        if ($cost == 0) return 'Infinite';
        $annualSavings = $monthlySavings * 12;
        $roi = (($annualSavings - $cost) / $cost) * 100;
        return round($roi, 0) . '%';
    }
}
