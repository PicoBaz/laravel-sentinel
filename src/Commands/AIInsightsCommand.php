<?php

namespace PicoBaz\Sentinel\Commands;

use Illuminate\Console\Command;
use PicoBaz\Sentinel\Modules\AIInsights\AIInsightsHelper;
use PicoBaz\Sentinel\Modules\AIInsights\AIInsightsModule;

class AIInsightsCommand extends Command
{
    protected $signature = 'sentinel:ai-insights {--refresh}';
    protected $description = 'Display AI-powered insights and predictions';

    public function handle()
    {
        if ($this->option('refresh')) {
            $this->info('Refreshing AI insights...');
            $module = app(AIInsightsModule::class);
            $module->analyzePatterns();
            $module->detectAnomalies();
            $module->generatePredictions();
            $module->generateRecommendations();
            $this->info('Analysis complete!');
            $this->line('');
        }

        $this->displayHealthScore();
        $this->line('');
        
        $this->displayAnomalies();
        $this->line('');
        
        $this->displayPredictions();
        $this->line('');
        
        $this->displayRecommendations();
        $this->line('');
        
        $this->displayPatterns();
    }

    protected function displayHealthScore()
    {
        $score = AIInsightsHelper::getHealthScore();
        $status = AIInsightsHelper::getHealthStatus();
        
        $this->info('🏥 System Health');
        
        $color = match($status) {
            'excellent', 'good' => 'info',
            'fair' => 'comment',
            default => 'error',
        };
        
        $this->{$color}("Score: {$score}/100 - Status: " . strtoupper($status));
    }

    protected function displayAnomalies()
    {
        $anomalies = AIInsightsHelper::getAnomalies();
        
        $this->warn('⚠️  Anomalies Detected');
        
        $hasAnomalies = false;
        
        foreach ($anomalies as $type => $data) {
            if ($data && isset($data['detected']) && $data['detected']) {
                $hasAnomalies = true;
                $this->error("  {$type}: {$data['count']} anomalies");
                if (isset($data['threshold'])) {
                    $this->line("    Threshold: {$data['threshold']} | Max: {$data['max_value']}");
                }
            }
        }
        
        if (!$hasAnomalies) {
            $this->info('  No anomalies detected ✓');
        }
    }

    protected function displayPredictions()
    {
        $predictions = AIInsightsHelper::getPredictions();
        
        $this->info('🔮 Predictions');
        
        if (isset($predictions['performance'])) {
            $perf = $predictions['performance'];
            $emoji = $perf['trend'] === 'degrading' ? '📉' : '📈';
            $this->line("  {$emoji} Performance: {$perf['trend']}");
            $this->line("    Current: {$perf['current_avg']}ms | 24h: {$perf['prediction_24h']}ms | 7d: {$perf['prediction_7d']}ms");
        }
        
        if (isset($predictions['memory'])) {
            $mem = $predictions['memory'];
            $emoji = $mem['trend'] === 'increasing' ? '⬆️' : '⬇️';
            $this->line("  {$emoji} Memory: {$mem['trend']}");
            $this->line("    Current: {$mem['current_avg']}MB | 24h: {$mem['prediction_24h']}MB | 7d: {$mem['prediction_7d']}MB");
            if ($mem['threshold_breach_risk'] === 'high') {
                $this->error('    ⚠️  WARNING: Threshold breach predicted!');
            }
        }
        
        if (isset($predictions['downtime_risk'])) {
            $risk = $predictions['downtime_risk'];
            $this->line("  🎯 Downtime Risk: {$risk['level']} (Score: {$risk['score']})");
        }
    }

    protected function displayRecommendations()
    {
        $recommendations = AIInsightsHelper::getRecommendations();
        
        $this->info('💡 AI Recommendations');
        
        if (empty($recommendations)) {
            $this->info('  No recommendations at this time ✓');
            return;
        }
        
        foreach ($recommendations as $rec) {
            $priorityColor = match($rec['priority']) {
                'critical' => 'error',
                'high' => 'warn',
                default => 'info',
            };
            
            $emoji = match($rec['priority']) {
                'critical' => '🚨',
                'high' => '⚠️',
                'medium' => '📌',
                default => 'ℹ️',
            };
            
            $this->{$priorityColor}("  {$emoji} [{$rec['priority']}] {$rec['title']}");
            $this->line("    {$rec['description']}");
            $this->comment("    → {$rec['action']}");
            $this->line('');
        }
    }

    protected function displayPatterns()
    {
        $patterns = AIInsightsHelper::getPatterns();
        
        $this->info('📊 Patterns Analysis');
        
        if (isset($patterns['peak_hours']['hours']) && !empty($patterns['peak_hours']['hours'])) {
            $hours = implode(', ', array_map(fn($h) => $h . ':00', $patterns['peak_hours']['hours']));
            $this->line("  🕐 Peak Hours: {$hours}");
            $this->line("    Average Load: {$patterns['peak_hours']['average_load']} | Peak: {$patterns['peak_hours']['peak_load']}");
        }
        
        if (isset($patterns['slow_endpoints']) && !empty($patterns['slow_endpoints'])) {
            $this->line('  🐌 Slowest Endpoints:');
            $count = 0;
            foreach ($patterns['slow_endpoints'] as $url => $stats) {
                if (++$count > 5) break;
                $this->line("    {$url}: {$stats['avg_time']}ms avg ({$stats['count']} requests)");
            }
        }
    }
}
