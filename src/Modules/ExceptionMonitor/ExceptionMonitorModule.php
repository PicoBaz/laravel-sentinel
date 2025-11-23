<?php

namespace PicoBaz\Sentinel\Modules\ExceptionMonitor;

use Illuminate\Support\Facades\Event;
use PicoBaz\Sentinel\Facades\Sentinel;

class ExceptionMonitorModule
{
    public function boot()
    {
        if (!config('sentinel.modules.exceptionMonitor')) {
            return;
        }

        app('events')->listen('Illuminate\Log\Events\MessageLogged', function ($event) {
            if ($event->level === 'error' || $event->level === 'critical') {
                Sentinel::log('exception', [
                    'message' => $event->message,
                    'level' => $event->level,
                    'context' => $event->context,
                ]);
            }
        });
    }
}
