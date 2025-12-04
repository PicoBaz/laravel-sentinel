<?php

namespace PicoBaz\Sentinel\Modules\CostOptimizer;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use PicoBaz\Sentinel\Facades\Sentinel;
use PicoBaz\Sentinel\Models\SentinelLog;

class CostOptimizerModule
{
    protected $providerRates = [
        'aws' => [
            't3.micro' => 0.0104,
            't3.small' => 0.0208,
            't3.medium' => 0.0416,
            't3.large' => 0.0832,
            'rds.t3.micro' => 0.017,
            'rds.t3.small' => 0.034,
            's3_storage_gb' => 0.023,
            'cloudfront_gb' => 0.085,
        ],
        'digitalocean' => [
            'basic_1gb' => 0.00744,
            'basic_2gb' => 0.01488,
            'basic_4gb' => 0.02976,
            'basic_8gb' => 0.05952,
            'database_1gb' => 0.02083,
            'spaces_gb' => 0.005,
        ],
        'linode' => [
            'nanode' => 0.0075,
            'linode_2gb' => 0.015,
            'linode_4gb' => 0.030,
            'linode_8gb' => 0.060,
        ],
    ];

    public function boot()
    {
        if (!config('sentinel.modules.costOptimizer')) {
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
                $this->analyzeCosts();
                $this->generateOptimizations();
            })->daily()->name('sentinel-cost-analysis');
        });
    }

    public function analyzeCosts()
    {
        $analysis = [
            'compute' => $this->analyzeComputeCosts(),
            'database' => $this->analyzeDatabaseCosts(),
            'storage' => $this->analyzeStorageCosts(),
            'network' => $this->analyzeNetworkCosts(),
            'cache' => $this->analyzeCacheCosts(),
        ];

        $analysis['total_monthly'] = $this->calculateTotalMonthlyCost($analysis);
        $analysis['projected_yearly'] = $analysis['total_monthly'] * 12;
        $analysis['cost_per_request'] = $this->calculateCostPerRequest($analysis);

        Cache::put('sentinel:cost:analysis', $analysis, now()->addDay());

        return $analysis;
    }

    protected function analyzeComputeCosts()
    {
        $provider = config('sentinel.cost_optimizer.provider', 'aws');
        $instanceType = config('sentinel.cost_optimizer.instance_type', 't3.small');
        $instanceCount = config('sentinel.cost_optimizer.instance_count', 1);

        $hourlyRate = $this->providerRates[$provider][$instanceType] ?? 0.02;
        $monthlyCost = $hourlyRate * 730 * $instanceCount;

        $logs = SentinelLog::where('type', 'performance')
            ->where('created_at', '>=', now()->subDays(7))
            ->get();

        $avgResponseTime = $logs->avg('data.response_time') ?? 0;
        $peakResponseTime = $logs->max('data.response_time') ?? 0;

        $utilizationEstimate = min(100, ($avgResponseTime / 1000) * 100);

        $recommendation = null;
        if ($utilizationEstimate < 30) {
            $recommendation = 'downgrade';
            $savings = $this->calculateDowngradeSavings($provider, $instanceType);
        } elseif ($utilizationEstimate > 80) {
            $recommendation = 'upgrade';
            $additionalCost = $this->calculateUpgradeCost($provider, $instanceType);
        }

        return [
            'provider' => $provider,
            'instance_type' => $instanceType,
            'instance_count' => $instanceCount,
            'hourly_rate' => round($hourlyRate, 4),
            'monthly_cost' => round($monthlyCost, 2),
            'utilization' => round($utilizationEstimate, 2),
            'avg_response_time' => round($avgResponseTime, 2),
            'peak_response_time' => round($peakResponseTime, 2),
            'recommendation' => $recommendation,
            'potential_savings' => $savings ?? null,
            'additional_cost' => $additionalCost ?? null,
        ];
    }

    protected function analyzeDatabaseCosts()
    {
        $provider = config('sentinel.cost_optimizer.db_provider', 'aws');
        $dbType = config('sentinel.cost_optimizer.db_type', 'rds.t3.small');

        $hourlyRate = $this->providerRates[$provider][$dbType] ?? 0.034;
        $monthlyCost = $hourlyRate * 730;

        $queryLogs = SentinelLog::where('type', 'query')
            ->where('created_at', '>=', now()->subDays(7))
            ->get();

        $slowQueries = $queryLogs->where('data.time', '>', 1000)->count();
        $avgQueryTime = $queryLogs->avg('data.time') ?? 0;
        $totalQueries = $queryLogs->count();

        $indexingScore = 100 - min(100, ($slowQueries / max(1, $totalQueries)) * 100 * 10);

        $optimizations = [];
        
        if ($slowQueries > 10) {
            $optimizations[] = [
                'type' => 'indexing',
                'description' => 'Add database indexes to slow queries',
                'impact' => 'high',
                'potential_speedup' => '50-90%',
                'cost_impact' => 0,
            ];
        }

        if ($avgQueryTime > 500) {
            $optimizations[] = [
                'type' => 'query_optimization',
                'description' => 'Optimize N+1 queries and use eager loading',
                'impact' => 'high',
                'potential_speedup' => '40-80%',
                'cost_impact' => 0,
            ];
        }

        $cacheOpportunity = $this->calculateCacheOpportunity($queryLogs);
        if ($cacheOpportunity > 30) {
            $optimizations[] = [
                'type' => 'query_caching',
                'description' => 'Implement query result caching',
                'impact' => 'medium',
                'potential_speedup' => '30-60%',
                'cacheable_queries' => $cacheOpportunity . '%',
            ];
        }

        return [
            'provider' => $provider,
            'type' => $dbType,
            'hourly_rate' => round($hourlyRate, 4),
            'monthly_cost' => round($monthlyCost, 2),
            'total_queries' => $totalQueries,
            'slow_queries' => $slowQueries,
            'avg_query_time' => round($avgQueryTime, 2),
            'indexing_score' => round($indexingScore, 2),
            'optimizations' => $optimizations,
        ];
    }

    protected function analyzeStorageCosts()
    {
        $provider = config('sentinel.cost_optimizer.storage_provider', 'aws');
        $storageGb = config('sentinel.cost_optimizer.storage_gb', 100);

        $ratePerGb = $this->providerRates[$provider]['s3_storage_gb'] ?? 0.023;
        $monthlyCost = $ratePerGb * $storageGb;

        $recommendations = [];

        if ($storageGb > 1000) {
            $recommendations[] = [
                'type' => 'compression',
                'description' => 'Enable compression for static assets',
                'potential_savings' => round($monthlyCost * 0.3, 2),
                'savings_percent' => 30,
            ];
        }

        $recommendations[] = [
            'type' => 'lifecycle_policy',
            'description' => 'Implement storage lifecycle policies',
            'potential_savings' => round($monthlyCost * 0.2, 2),
            'savings_percent' => 20,
        ];

        return [
            'provider' => $provider,
            'storage_gb' => $storageGb,
            'rate_per_gb' => $ratePerGb,
            'monthly_cost' => round($monthlyCost, 2),
            'recommendations' => $recommendations,
        ];
    }

    protected function analyzeNetworkCosts()
    {
        $provider = config('sentinel.cost_optimizer.cdn_provider', 'aws');
        $bandwidthGb = config('sentinel.cost_optimizer.bandwidth_gb', 500);

        $ratePerGb = $this->providerRates[$provider]['cloudfront_gb'] ?? 0.085;
        $monthlyCost = $ratePerGb * $bandwidthGb;

        $performanceLogs = SentinelLog::where('type', 'performance')
            ->where('created_at', '>=', now()->subDays(7))
            ->get();

        $avgResponseSize = 50;
        $totalRequests = $performanceLogs->count() * 100;
        $estimatedBandwidth = ($avgResponseSize * $totalRequests) / 1024 / 1024 / 1024;

        $cdnHitRate = config('sentinel.cost_optimizer.cdn_hit_rate', 70);

        $optimizations = [];

        if ($cdnHitRate < 80) {
            $potentialSavings = $monthlyCost * ((80 - $cdnHitRate) / 100);
            $optimizations[] = [
                'type' => 'cdn_optimization',
                'description' => 'Optimize CDN cache settings',
                'current_hit_rate' => $cdnHitRate,
                'target_hit_rate' => 80,
                'potential_savings' => round($potentialSavings, 2),
            ];
        }

        $optimizations[] = [
            'type' => 'image_optimization',
            'description' => 'Implement WebP format and lazy loading',
            'potential_savings' => round($monthlyCost * 0.4, 2),
            'bandwidth_reduction' => '40%',
        ];

        return [
            'provider' => $provider,
            'bandwidth_gb' => $bandwidthGb,
            'rate_per_gb' => $ratePerGb,
            'monthly_cost' => round($monthlyCost, 2),
            'cdn_hit_rate' => $cdnHitRate,
            'estimated_requests' => $totalRequests,
            'optimizations' => $optimizations,
        ];
    }

    protected function analyzeCacheCosts()
    {
        $cacheDriver = config('cache.default');
        $monthlyCost = 0;

        if (in_array($cacheDriver, ['redis', 'memcached'])) {
            $provider = config('sentinel.cost_optimizer.cache_provider', 'aws');
            $instanceType = config('sentinel.cost_optimizer.cache_instance', 'cache.t3.micro');
            
            $hourlyRate = 0.017;
            $monthlyCost = $hourlyRate * 730;
        }

        $queryLogs = SentinelLog::where('type', 'query')
            ->where('created_at', '>=', now()->subDays(7))
            ->get();

        $cacheableQueries = $this->identifyCacheableQueries($queryLogs);
        $cacheHitRateEstimate = 60;

        $performanceGain = [
            'query_reduction' => count($cacheableQueries) . ' queries',
            'time_saved' => round(collect($cacheableQueries)->sum('time') * 0.95, 2) . 'ms',
        ];

        return [
            'driver' => $cacheDriver,
            'monthly_cost' => round($monthlyCost, 2),
            'cacheable_queries' => count($cacheableQueries),
            'estimated_hit_rate' => $cacheHitRateEstimate,
            'performance_gain' => $performanceGain,
            'roi' => $this->calculateCacheROI($monthlyCost, $cacheableQueries),
        ];
    }

    public function generateOptimizations()
    {
        $analysis = Cache::get('sentinel:cost:analysis', []);
        
        $optimizations = [];

        if (isset($analysis['compute']['utilization'])) {
            if ($analysis['compute']['utilization'] < 30) {
                $optimizations[] = [
                    'category' => 'compute',
                    'priority' => 'high',
                    'title' => 'Downsize Compute Resources',
                    'description' => 'Server utilization is below 30%',
                    'action' => 'Consider downgrading to smaller instance',
                    'savings' => $analysis['compute']['potential_savings'] ?? 0,
                    'implementation_time' => '1 hour',
                    'risk' => 'low',
                ];
            } elseif ($analysis['compute']['utilization'] > 80) {
                $optimizations[] = [
                    'category' => 'compute',
                    'priority' => 'critical',
                    'title' => 'Upgrade Compute Resources',
                    'description' => 'Server utilization exceeds 80%',
                    'action' => 'Upgrade to larger instance or implement auto-scaling',
                    'cost_increase' => $analysis['compute']['additional_cost'] ?? 0,
                    'implementation_time' => '2 hours',
                    'risk' => 'medium',
                ];
            }
        }

        if (isset($analysis['database']['optimizations']) && !empty($analysis['database']['optimizations'])) {
            foreach ($analysis['database']['optimizations'] as $opt) {
                $optimizations[] = [
                    'category' => 'database',
                    'priority' => $opt['impact'] === 'high' ? 'high' : 'medium',
                    'title' => $opt['description'],
                    'action' => $this->getActionForOptimization($opt['type']),
                    'savings' => 0,
                    'performance_gain' => $opt['potential_speedup'] ?? 'N/A',
                    'implementation_time' => '2-4 hours',
                    'risk' => 'low',
                ];
            }
        }

        if (isset($analysis['network']['optimizations'])) {
            foreach ($analysis['network']['optimizations'] as $opt) {
                $optimizations[] = [
                    'category' => 'network',
                    'priority' => 'medium',
                    'title' => $opt['description'],
                    'savings' => $opt['potential_savings'] ?? 0,
                    'implementation_time' => '3-6 hours',
                    'risk' => 'low',
                ];
            }
        }

        if (isset($analysis['storage']['recommendations'])) {
            foreach ($analysis['storage']['recommendations'] as $rec) {
                $optimizations[] = [
                    'category' => 'storage',
                    'priority' => 'low',
                    'title' => $rec['description'],
                    'savings' => $rec['potential_savings'] ?? 0,
                    'implementation_time' => '1-2 hours',
                    'risk' => 'low',
                ];
            }
        }

        usort($optimizations, function ($a, $b) {
            $priorityOrder = ['critical' => 4, 'high' => 3, 'medium' => 2, 'low' => 1];
            return ($priorityOrder[$b['priority']] ?? 0) - ($priorityOrder[$a['priority']] ?? 0);
        });

        Cache::put('sentinel:cost:optimizations', $optimizations, now()->addDay());

        $totalSavings = array_sum(array_column($optimizations, 'savings'));

        if ($totalSavings > 50) {
            Sentinel::log('cost_optimization', [
                'type' => 'high_savings_potential',
                'total_potential_savings' => $totalSavings,
                'optimization_count' => count($optimizations),
                'top_optimization' => $optimizations[0] ?? null,
            ]);
        }

        return $optimizations;
    }

    protected function calculateTotalMonthlyCost($analysis)
    {
        return array_sum([
            $analysis['compute']['monthly_cost'] ?? 0,
            $analysis['database']['monthly_cost'] ?? 0,
            $analysis['storage']['monthly_cost'] ?? 0,
            $analysis['network']['monthly_cost'] ?? 0,
            $analysis['cache']['monthly_cost'] ?? 0,
        ]);
    }

    protected function calculateCostPerRequest($analysis)
    {
        $logs = SentinelLog::where('type', 'performance')
            ->where('created_at', '>=', now()->subDay())
            ->count();

        $requestsPerMonth = $logs * 30;
        
        if ($requestsPerMonth == 0) {
            return 0;
        }

        return ($analysis['total_monthly'] / $requestsPerMonth) * 1000;
    }

    protected function calculateDowngradeSavings($provider, $currentType)
    {
        $types = array_keys($this->providerRates[$provider]);
        $currentIndex = array_search($currentType, $types);
        
        if ($currentIndex > 0) {
            $lowerType = $types[$currentIndex - 1];
            $currentRate = $this->providerRates[$provider][$currentType];
            $lowerRate = $this->providerRates[$provider][$lowerType];
            
            return round(($currentRate - $lowerRate) * 730, 2);
        }

        return 0;
    }

    protected function calculateUpgradeCost($provider, $currentType)
    {
        $types = array_keys($this->providerRates[$provider]);
        $currentIndex = array_search($currentType, $types);
        
        if ($currentIndex < count($types) - 1) {
            $higherType = $types[$currentIndex + 1];
            $currentRate = $this->providerRates[$provider][$currentType];
            $higherRate = $this->providerRates[$provider][$higherType];
            
            return round(($higherRate - $currentRate) * 730, 2);
        }

        return 0;
    }

    protected function calculateCacheOpportunity($queryLogs)
    {
        $repeatQueries = $queryLogs->groupBy('data.sql')
            ->filter(function ($group) {
                return $group->count() > 5;
            });

        return min(100, ($repeatQueries->count() / max(1, $queryLogs->count())) * 100);
    }

    protected function identifyCacheableQueries($queryLogs)
    {
        return $queryLogs->groupBy('data.sql')
            ->filter(function ($group) {
                return $group->count() > 5 && !str_contains(strtolower($group->first()->data['sql'] ?? ''), 'insert');
            })
            ->map(function ($group) {
                return [
                    'sql' => $group->first()->data['sql'] ?? '',
                    'count' => $group->count(),
                    'time' => $group->avg('data.time'),
                ];
            })
            ->values()
            ->toArray();
    }

    protected function calculateCacheROI($monthlyCost, $cacheableQueries)
    {
        if (empty($cacheableQueries)) {
            return 'N/A';
        }

        $timeSaved = collect($cacheableQueries)->sum('time') * 0.95;
        $valueOfTimeSaved = ($timeSaved / 1000 / 3600) * 50;

        $roi = (($valueOfTimeSaved - $monthlyCost) / $monthlyCost) * 100;

        return round($roi, 2) . '%';
    }

    protected function getActionForOptimization($type)
    {
        return match($type) {
            'indexing' => 'Run: php artisan sentinel:analyze-indexes',
            'query_optimization' => 'Review queries with: php artisan sentinel:query-report',
            'query_caching' => 'Implement Redis cache for frequent queries',
            default => 'See documentation for implementation steps',
        };
    }
}
