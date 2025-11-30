<?php

namespace PicoBaz\Sentinel\Modules\AIInsights;

use Illuminate\Support\Facades\Cache;
use PicoBaz\Sentinel\Facades\Sentinel;
use PicoBaz\Sentinel\Models\SentinelLog;

class AIInsightsModule
{
    protected $predictionWindow = 24;
    protected $anomalyThreshold = 2.5;

    public function boot()
    {
        if (!config('sentinel.modules.aiInsights')) {
            return;
        }

        $this->scheduleAnalysis();
    }

    protected function scheduleAnalysis()
    {
        if (!app()->runningInConsole()) {
            return;
        }

        app()->booted(function () {
            $schedule = app()->make(\Illuminate\Console\Scheduling\Schedule::class);
            
            $schedule->call(function () {
                $this->analyzePatterns();
                $this->detectAnomalies();
                $this->generatePredictions();
                $this->generateRecommendations();
            })->hourly()->name('sentinel-ai-analysis');
        });
    }

    public function analyzePatterns()
    {
        $logs = SentinelLog::where('created_at', '>=', now()->subDays(7))->get();

        $patterns = [
            'peak_hours' => $this->identifyPeakHours($logs),
            'slow_endpoints' => $this->identifySlowEndpoints($logs),
            'memory_trends' => $this->analyzeMemoryTrends($logs),
            'error_patterns' => $this->analyzeErrorPatterns($logs),
        ];

        Cache::put('sentinel:ai:patterns', $patterns, now()->addHours(2));

        return $patterns;
    }

    protected function identifyPeakHours($logs)
    {
        $hourlyDistribution = $logs->groupBy(function ($log) {
            return $log->created_at->format('H');
        })->map->count()->sortDesc();

        $average = $hourlyDistribution->avg();
        $peakHours = $hourlyDistribution->filter(function ($count) use ($average) {
            return $count > $average * 1.5;
        });

        return [
            'hours' => $peakHours->keys()->toArray(),
            'average_load' => round($average, 2),
            'peak_load' => $peakHours->max(),
        ];
    }

    protected function identifySlowEndpoints($logs)
    {
        $performanceLogs = $logs->where('type', 'performance');

        $endpoints = $performanceLogs->groupBy('data.url')
            ->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'avg_time' => round($group->avg('data.response_time'), 2),
                    'max_time' => $group->max('data.response_time'),
                ];
            })
            ->sortByDesc('avg_time')
            ->take(10);

        return $endpoints->toArray();
    }

    protected function analyzeMemoryTrends($logs)
    {
        $memoryLogs = $logs->where('type', 'memory');

        if ($memoryLogs->isEmpty()) {
            return null;
        }

        $trend = $this->calculateTrend(
            $memoryLogs->pluck('data.usage')->toArray()
        );

        return [
            'current_avg' => round($memoryLogs->avg('data.usage'), 2),
            'trend' => $trend > 0 ? 'increasing' : 'decreasing',
            'trend_rate' => abs(round($trend, 2)),
            'prediction_7d' => round($memoryLogs->avg('data.usage') + ($trend * 168), 2),
        ];
    }

    protected function analyzeErrorPatterns($logs)
    {
        $errorLogs = $logs->where('type', 'exception');

        $patterns = $errorLogs->groupBy('data.message')
            ->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'first_seen' => $group->min('created_at'),
                    'last_seen' => $group->max('created_at'),
                    'frequency' => $this->calculateFrequency($group),
                ];
            })
            ->sortByDesc('count');

        return $patterns->toArray();
    }

    public function detectAnomalies()
    {
        $logs = SentinelLog::where('created_at', '>=', now()->subHours($this->predictionWindow))->get();

        $anomalies = [];

        $anomalies['response_time'] = $this->detectResponseTimeAnomalies($logs);
        $anomalies['memory_usage'] = $this->detectMemoryAnomalies($logs);
        $anomalies['error_rate'] = $this->detectErrorRateAnomalies($logs);
        $anomalies['query_count'] = $this->detectQueryCountAnomalies($logs);

        $criticalAnomalies = collect($anomalies)->filter()->flatten(1);

        if ($criticalAnomalies->isNotEmpty()) {
            Sentinel::log('ai_insight', [
                'type' => 'anomaly_detected',
                'anomalies' => $criticalAnomalies->toArray(),
                'severity' => 'warning',
                'timestamp' => now()->toDateTimeString(),
            ]);
        }

        Cache::put('sentinel:ai:anomalies', $anomalies, now()->addHours(1));

        return $anomalies;
    }

    protected function detectResponseTimeAnomalies($logs)
    {
        $performanceLogs = $logs->where('type', 'performance');

        if ($performanceLogs->count() < 10) {
            return null;
        }

        $times = $performanceLogs->pluck('data.response_time')->toArray();
        $stats = $this->calculateStatistics($times);

        $anomalies = collect($times)->filter(function ($time) use ($stats) {
            return abs($time - $stats['mean']) > ($this->anomalyThreshold * $stats['std_dev']);
        });

        if ($anomalies->isNotEmpty()) {
            return [
                'detected' => true,
                'count' => $anomalies->count(),
                'threshold' => round($stats['mean'] + ($this->anomalyThreshold * $stats['std_dev']), 2),
                'max_value' => $anomalies->max(),
            ];
        }

        return null;
    }

    protected function detectMemoryAnomalies($logs)
    {
        $memoryLogs = $logs->where('type', 'memory');

        if ($memoryLogs->count() < 10) {
            return null;
        }

        $usage = $memoryLogs->pluck('data.usage')->toArray();
        $stats = $this->calculateStatistics($usage);

        $anomalies = collect($usage)->filter(function ($mem) use ($stats) {
            return abs($mem - $stats['mean']) > ($this->anomalyThreshold * $stats['std_dev']);
        });

        if ($anomalies->isNotEmpty()) {
            return [
                'detected' => true,
                'count' => $anomalies->count(),
                'threshold' => round($stats['mean'] + ($this->anomalyThreshold * $stats['std_dev']), 2),
                'max_value' => $anomalies->max(),
            ];
        }

        return null;
    }

    protected function detectErrorRateAnomalies($logs)
    {
        $hourlyErrors = $logs->where('type', 'exception')
            ->groupBy(function ($log) {
                return $log->created_at->format('Y-m-d H:00');
            })
            ->map->count();

        if ($hourlyErrors->count() < 5) {
            return null;
        }

        $rates = $hourlyErrors->values()->toArray();
        $stats = $this->calculateStatistics($rates);

        $anomalies = $hourlyErrors->filter(function ($rate) use ($stats) {
            return $rate > ($stats['mean'] + ($this->anomalyThreshold * $stats['std_dev']));
        });

        if ($anomalies->isNotEmpty()) {
            return [
                'detected' => true,
                'hours' => $anomalies->keys()->toArray(),
                'normal_rate' => round($stats['mean'], 2),
                'peak_rate' => $anomalies->max(),
            ];
        }

        return null;
    }

    protected function detectQueryCountAnomalies($logs)
    {
        $hourlyQueries = $logs->where('type', 'query')
            ->groupBy(function ($log) {
                return $log->created_at->format('Y-m-d H:00');
            })
            ->map->count();

        if ($hourlyQueries->count() < 5) {
            return null;
        }

        $counts = $hourlyQueries->values()->toArray();
        $stats = $this->calculateStatistics($counts);

        $anomalies = $hourlyQueries->filter(function ($count) use ($stats) {
            return $count > ($stats['mean'] + ($this->anomalyThreshold * $stats['std_dev']));
        });

        if ($anomalies->isNotEmpty()) {
            return [
                'detected' => true,
                'hours' => $anomalies->keys()->toArray(),
                'normal_count' => round($stats['mean'], 2),
                'peak_count' => $anomalies->max(),
            ];
        }

        return null;
    }

    public function generatePredictions()
    {
        $logs = SentinelLog::where('created_at', '>=', now()->subDays(7))->get();

        $predictions = [
            'performance' => $this->predictPerformance($logs),
            'memory' => $this->predictMemoryUsage($logs),
            'error_rate' => $this->predictErrorRate($logs),
            'downtime_risk' => $this->predictDowntimeRisk($logs),
        ];

        Cache::put('sentinel:ai:predictions', $predictions, now()->addHours(6));

        return $predictions;
    }

    protected function predictPerformance($logs)
    {
        $performanceLogs = $logs->where('type', 'performance');

        if ($performanceLogs->count() < 20) {
            return null;
        }

        $times = $performanceLogs->pluck('data.response_time')->toArray();
        $trend = $this->calculateTrend($times);

        $currentAvg = array_sum($times) / count($times);
        $prediction24h = $currentAvg + ($trend * 24);
        $prediction7d = $currentAvg + ($trend * 168);

        return [
            'current_avg' => round($currentAvg, 2),
            'trend' => $trend > 0 ? 'degrading' : 'improving',
            'prediction_24h' => round($prediction24h, 2),
            'prediction_7d' => round($prediction7d, 2),
            'confidence' => $this->calculateConfidence(count($times)),
        ];
    }

    protected function predictMemoryUsage($logs)
    {
        $memoryLogs = $logs->where('type', 'memory');

        if ($memoryLogs->count() < 20) {
            return null;
        }

        $usage = $memoryLogs->pluck('data.usage')->toArray();
        $trend = $this->calculateTrend($usage);

        $currentAvg = array_sum($usage) / count($usage);
        $prediction24h = $currentAvg + ($trend * 24);
        $prediction7d = $currentAvg + ($trend * 168);

        return [
            'current_avg' => round($currentAvg, 2),
            'trend' => $trend > 0 ? 'increasing' : 'decreasing',
            'prediction_24h' => round($prediction24h, 2),
            'prediction_7d' => round($prediction7d, 2),
            'threshold_breach_risk' => $prediction7d > config('sentinel.thresholds.memory_usage') ? 'high' : 'low',
        ];
    }

    protected function predictErrorRate($logs)
    {
        $dailyErrors = $logs->where('type', 'exception')
            ->groupBy(function ($log) {
                return $log->created_at->format('Y-m-d');
            })
            ->map->count()
            ->values()
            ->toArray();

        if (count($dailyErrors) < 3) {
            return null;
        }

        $trend = $this->calculateTrend($dailyErrors);
        $currentRate = end($dailyErrors);
        $prediction7d = $currentRate + ($trend * 7);

        return [
            'current_rate' => $currentRate,
            'trend' => $trend > 0 ? 'increasing' : 'decreasing',
            'prediction_7d' => round(max(0, $prediction7d), 2),
            'severity' => $trend > 2 ? 'critical' : ($trend > 0 ? 'warning' : 'normal'),
        ];
    }

    protected function predictDowntimeRisk($logs)
    {
        $criticalLogs = $logs->where('severity', 'critical');
        $errorCount = $logs->where('type', 'exception')->count();
        $performanceIssues = $logs->where('type', 'performance')
            ->where('data.response_time', '>', config('sentinel.thresholds.response_time') * 2)
            ->count();

        $riskScore = 0;

        $riskScore += min($criticalLogs->count() * 10, 40);
        $riskScore += min($errorCount * 0.5, 30);
        $riskScore += min($performanceIssues * 2, 30);

        $riskLevel = 'low';
        if ($riskScore > 70) $riskLevel = 'critical';
        elseif ($riskScore > 50) $riskLevel = 'high';
        elseif ($riskScore > 30) $riskLevel = 'medium';

        return [
            'score' => round($riskScore, 2),
            'level' => $riskLevel,
            'factors' => [
                'critical_issues' => $criticalLogs->count(),
                'error_count' => $errorCount,
                'performance_issues' => $performanceIssues,
            ],
        ];
    }

    public function generateRecommendations()
    {
        $patterns = Cache::get('sentinel:ai:patterns', []);
        $anomalies = Cache::get('sentinel:ai:anomalies', []);
        $predictions = Cache::get('sentinel:ai:predictions', []);

        $recommendations = [];

        if (isset($patterns['slow_endpoints']) && !empty($patterns['slow_endpoints'])) {
            $recommendations[] = [
                'type' => 'performance',
                'priority' => 'high',
                'title' => 'Optimize Slow Endpoints',
                'description' => 'Multiple endpoints showing high response times',
                'action' => 'Review and optimize the following endpoints: ' . implode(', ', array_keys(array_slice($patterns['slow_endpoints'], 0, 3))),
            ];
        }

        if (isset($predictions['memory']['threshold_breach_risk']) && $predictions['memory']['threshold_breach_risk'] === 'high') {
            $recommendations[] = [
                'type' => 'memory',
                'priority' => 'critical',
                'title' => 'Memory Threshold Breach Predicted',
                'description' => 'Memory usage is predicted to exceed threshold within 7 days',
                'action' => 'Investigate memory leaks and optimize memory-intensive operations',
            ];
        }

        if (isset($predictions['downtime_risk']['level']) && in_array($predictions['downtime_risk']['level'], ['high', 'critical'])) {
            $recommendations[] = [
                'type' => 'availability',
                'priority' => 'critical',
                'title' => 'High Downtime Risk Detected',
                'description' => 'System stability is at risk based on current trends',
                'action' => 'Immediate attention required - Review critical issues and performance metrics',
            ];
        }

        if (isset($patterns['peak_hours']['hours']) && !empty($patterns['peak_hours']['hours'])) {
            $recommendations[] = [
                'type' => 'scaling',
                'priority' => 'medium',
                'title' => 'Scale During Peak Hours',
                'description' => 'Consistent high load detected during specific hours',
                'action' => 'Consider auto-scaling during peak hours: ' . implode(', ', $patterns['peak_hours']['hours']) . ':00',
            ];
        }

        Cache::put('sentinel:ai:recommendations', $recommendations, now()->addHours(6));

        if (!empty($recommendations)) {
            Sentinel::log('ai_insight', [
                'type' => 'recommendations_generated',
                'count' => count($recommendations),
                'recommendations' => $recommendations,
                'timestamp' => now()->toDateTimeString(),
            ]);
        }

        return $recommendations;
    }

    protected function calculateStatistics(array $values)
    {
        if (empty($values)) {
            return ['mean' => 0, 'std_dev' => 0];
        }

        $mean = array_sum($values) / count($values);
        
        $variance = array_sum(array_map(function ($x) use ($mean) {
            return pow($x - $mean, 2);
        }, $values)) / count($values);
        
        $stdDev = sqrt($variance);

        return [
            'mean' => $mean,
            'std_dev' => $stdDev,
            'min' => min($values),
            'max' => max($values),
        ];
    }

    protected function calculateTrend(array $values)
    {
        $n = count($values);
        if ($n < 2) return 0;

        $x = range(1, $n);
        $y = $values;

        $sumX = array_sum($x);
        $sumY = array_sum($y);
        $sumXY = array_sum(array_map(function ($xi, $yi) {
            return $xi * $yi;
        }, $x, $y));
        $sumX2 = array_sum(array_map(function ($xi) {
            return $xi * $xi;
        }, $x));

        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);

        return $slope;
    }

    protected function calculateFrequency($group)
    {
        if ($group->count() < 2) return 'rare';

        $firstSeen = $group->min('created_at');
        $lastSeen = $group->max('created_at');
        $hoursDiff = $lastSeen->diffInHours($firstSeen);

        if ($hoursDiff == 0) return 'multiple';

        $rate = $group->count() / $hoursDiff;

        if ($rate > 1) return 'frequent';
        if ($rate > 0.1) return 'moderate';
        return 'rare';
    }

    protected function calculateConfidence($sampleSize)
    {
        if ($sampleSize < 10) return 'low';
        if ($sampleSize < 50) return 'medium';
        if ($sampleSize < 100) return 'high';
        return 'very_high';
    }
}
