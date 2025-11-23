<?php

namespace PicoBaz\Sentinel\Services;

use Illuminate\Support\Facades\DB;
use PicoBaz\Sentinel\Events\AlertTriggered;
use PicoBaz\Sentinel\Models\SentinelLog;

class SentinelService
{
    protected $app;
    protected $modules = [];

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function registerModule(string $name, $module)
    {
        $this->modules[$name] = $module;
        return $this;
    }

    public function log(string $type, array $data)
    {
        $log = SentinelLog::create([
            'type' => $type,
            'data' => $data,
            'severity' => $this->calculateSeverity($type, $data),
            'created_at' => now(),
        ]);

        if ($this->shouldTriggerAlert($type, $data)) {
            event(new AlertTriggered($log));
        }

        return $log;
    }

    public function getMetrics(string $type = null, int $hours = 24)
    {
        $query = SentinelLog::where('created_at', '>=', now()->subHours($hours));

        if ($type) {
            $query->where('type', $type);
        }

        return $query->get();
    }

    public function getStatistics()
    {
        return [
            'total_logs' => SentinelLog::count(),
            'today_logs' => SentinelLog::whereDate('created_at', today())->count(),
            'critical_logs' => SentinelLog::where('severity', 'critical')->count(),
            'average_response_time' => $this->getAverageResponseTime(),
            'slow_queries_count' => $this->getSlowQueriesCount(),
        ];
    }

    protected function calculateSeverity(string $type, array $data): string
    {
        $thresholds = config('sentinel.thresholds');

        if ($type === 'query' && isset($data['time'])) {
            if ($data['time'] > $thresholds['query_time'] * 3) {
                return 'critical';
            } elseif ($data['time'] > $thresholds['query_time']) {
                return 'warning';
            }
        }

        if ($type === 'memory' && isset($data['usage'])) {
            if ($data['usage'] > $thresholds['memory_usage'] * 1.5) {
                return 'critical';
            } elseif ($data['usage'] > $thresholds['memory_usage']) {
                return 'warning';
            }
        }

        if ($type === 'exception') {
            return 'critical';
        }

        return 'info';
    }

    protected function shouldTriggerAlert(string $type, array $data): bool
    {
        return $this->calculateSeverity($type, $data) !== 'info';
    }

    protected function getAverageResponseTime()
    {
        return SentinelLog::where('type', 'performance')
            ->where('created_at', '>=', now()->subDay())
            ->avg('data->response_time') ?? 0;
    }

    protected function getSlowQueriesCount()
    {
        return SentinelLog::where('type', 'query')
            ->where('severity', '!=', 'info')
            ->where('created_at', '>=', now()->subDay())
            ->count();
    }
}
