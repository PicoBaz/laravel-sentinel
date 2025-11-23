<?php

namespace PicoBaz\Sentinel\Modules\PerformanceMonitor;

use Illuminate\Support\Facades\Event;
use PicoBaz\Sentinel\Facades\Sentinel;

class PerformanceMonitorModule
{
    protected $startTime;

    public function boot()
    {
        if (!config('sentinel.modules.performanceMonitor')) {
            return;
        }

        $this->startTime = defined('LARAVEL_START') ? LARAVEL_START : microtime(true);

        app()->terminating(function () {
            $responseTime = (microtime(true) - $this->startTime) * 1000;
            $threshold = config('sentinel.thresholds.response_time');

            if ($responseTime > $threshold) {
                Sentinel::log('performance', [
                    'response_time' => round($responseTime, 2),
                    'threshold' => $threshold,
                    'url' => request()->fullUrl(),
                    'method' => request()->method(),
                ]);
            }
        });
    }
}
