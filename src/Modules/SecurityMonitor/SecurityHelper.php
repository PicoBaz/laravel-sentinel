<?php

namespace PicoBaz\Sentinel\Modules\SecurityMonitor;

use Illuminate\Support\Facades\Cache;
use PicoBaz\Sentinel\Facades\Sentinel;

class SecurityHelper
{
    public static function isIpBlacklisted(string $ip): bool
    {
        $blacklist = config('sentinel.security.blacklist', []);
        return in_array($ip, $blacklist);
    }

    public static function addToBlacklist(string $ip, string $reason = '')
    {
        $blacklist = config('sentinel.security.blacklist', []);
        
        if (!in_array($ip, $blacklist)) {
            $blacklist[] = $ip;
            
            Cache::put('sentinel:blacklist', $blacklist, now()->addDays(30));
            
            Sentinel::log('security', [
                'type' => 'ip_blacklisted',
                'ip' => $ip,
                'reason' => $reason,
                'timestamp' => now()->toDateTimeString(),
            ]);
        }
    }

    public static function getBlacklist(): array
    {
        return Cache::get('sentinel:blacklist', []);
    }

    public static function removeFromBlacklist(string $ip)
    {
        $blacklist = self::getBlacklist();
        $blacklist = array_diff($blacklist, [$ip]);
        
        Cache::put('sentinel:blacklist', $blacklist, now()->addDays(30));
        
        Sentinel::log('security', [
            'type' => 'ip_whitelisted',
            'ip' => $ip,
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    public static function getSecurityScore(string $ip): int
    {
        $threats = Sentinel::getMetrics('security', 24)
            ->where('data->ip', $ip)
            ->count();

        if ($threats === 0) return 100;
        if ($threats <= 3) return 80;
        if ($threats <= 10) return 50;
        if ($threats <= 20) return 20;
        
        return 0;
    }

    public static function getThreatLevel(int $score): string
    {
        if ($score >= 80) return 'low';
        if ($score >= 50) return 'medium';
        if ($score >= 20) return 'high';
        
        return 'critical';
    }

    public static function shouldBlockRequest(string $ip): bool
    {
        if (self::isIpBlacklisted($ip)) {
            return true;
        }

        $score = self::getSecurityScore($ip);
        $autoBlock = config('sentinel.security.auto_block_score', 20);

        if ($score <= $autoBlock) {
            self::addToBlacklist($ip, 'Auto-blocked: Low security score');
            return true;
        }

        return false;
    }
}
