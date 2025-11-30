<?php

namespace PicoBaz\Sentinel\Commands;

use Illuminate\Console\Command;
use PicoBaz\Sentinel\Facades\Sentinel;
use PicoBaz\Sentinel\Modules\SecurityMonitor\SecurityHelper;

class SecurityReportCommand extends Command
{
    protected $signature = 'sentinel:security-report {--hours=24}';
    protected $description = 'Generate security report for specified time period';

    public function handle()
    {
        $hours = $this->option('hours');
        
        $this->info("Security Report - Last {$hours} hours");
        $this->line('');

        $securityLogs = Sentinel::getMetrics('security', $hours);

        $failedLogins = $securityLogs->where('data.type', 'failed_login')->count();
        $suspiciousRequests = $securityLogs->where('data.type', 'suspicious_request')->count();
        $rateLimitExceeded = $securityLogs->where('data.type', 'rate_limit_exceeded')->count();
        $fileModifications = $securityLogs->where('data.type', 'file_modification')->count();

        $this->table(
            ['Threat Type', 'Count', 'Severity'],
            [
                ['Failed Logins', $failedLogins, $failedLogins > 10 ? 'High' : 'Low'],
                ['Suspicious Requests', $suspiciousRequests, $suspiciousRequests > 5 ? 'Critical' : 'Low'],
                ['Rate Limit Violations', $rateLimitExceeded, $rateLimitExceeded > 20 ? 'High' : 'Medium'],
                ['File Modifications', $fileModifications, $fileModifications > 0 ? 'Critical' : 'Low'],
            ]
        );

        $this->line('');
        $this->info('Top Threat IPs:');
        
        $topIps = $securityLogs
            ->groupBy('data.ip')
            ->map(fn($items) => $items->count())
            ->sortDesc()
            ->take(10);

        $ipData = [];
        foreach ($topIps as $ip => $count) {
            $score = SecurityHelper::getSecurityScore($ip);
            $threat = SecurityHelper::getThreatLevel($score);
            $ipData[] = [$ip, $count, $score, strtoupper($threat)];
        }

        $this->table(['IP Address', 'Threats', 'Score', 'Level'], $ipData);

        $blacklist = SecurityHelper::getBlacklist();
        if (count($blacklist) > 0) {
            $this->line('');
            $this->warn('Blacklisted IPs: ' . implode(', ', $blacklist));
        }

        $this->line('');
        $this->info('Total Security Events: ' . $securityLogs->count());
    }
}
