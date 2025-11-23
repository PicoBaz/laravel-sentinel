<?php

namespace PicoBaz\Sentinel\Events;

use PicoBaz\Sentinel\Models\SentinelLog;

class AlertTriggered
{
    public $log;

    public function __construct(SentinelLog $log)
    {
        $this->log = $log;
    }
}
