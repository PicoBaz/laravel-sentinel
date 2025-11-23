<?php

namespace PicoBaz\Sentinel\Modules\MemoryMonitor;

use PicoBaz\Sentinel\Facades\Sentinel;

class MemoryMonitorModule
{
    public function boot()
    {
        if (!config('sentinel.modules.memoryMonitor')) {
            return;
        }

        register_shutdown_function(function () {
            $usage = memory_get_peak_usage(true) / 1024 / 1024;
            $threshold = config('sentinel.thresholds.memory_usage');

            if ($usage > $threshold) {
                Sentinel::log('memory', [
                    'usage' => round($usage, 2),
                    'threshold' => $threshold,
                    'url' => request()->fullUrl(),
                ]);
            }
        });
    }
}
