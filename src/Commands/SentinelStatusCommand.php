<?php

namespace PicoBaz\Sentinel\Commands;

use Illuminate\Console\Command;
use PicoBaz\Sentinel\Facades\Sentinel;

class SentinelStatusCommand extends Command
{
    protected $signature = 'sentinel:status';
    protected $description = 'Show Sentinel monitoring status';

    public function handle()
    {
        $stats = Sentinel::getStatistics();

        $this->info('Laravel Sentinel Status');
        $this->line('');
        
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Logs', $stats['total_logs']],
                ['Today Logs', $stats['today_logs']],
                ['Critical Logs', $stats['critical_logs']],
                ['Avg Response Time', round($stats['average_response_time'], 2) . 'ms'],
                ['Slow Queries (24h)', $stats['slow_queries_count']],
            ]
        );
    }
}
