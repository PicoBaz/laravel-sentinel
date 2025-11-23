<?php

namespace PicoBaz\Sentinel\Modules\QueryMonitor;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use PicoBaz\Sentinel\Facades\Sentinel;

class QueryMonitorModule
{
    public function boot()
    {
        if (!config('sentinel.modules.queryMonitor')) {
            return;
        }

        DB::listen(function ($query) {
            $time = $query->time;
            $threshold = config('sentinel.thresholds.query_time');

            if ($time > $threshold) {
                Sentinel::log('query', [
                    'sql' => $query->sql,
                    'bindings' => $query->bindings,
                    'time' => $time,
                    'connection' => $query->connectionName,
                ]);
            }
        });
    }
}
