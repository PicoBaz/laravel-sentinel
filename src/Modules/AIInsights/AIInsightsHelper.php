<?php

namespace PicoBaz\Sentinel\Modules\AIInsights;

use Illuminate\Support\Facades\Cache;

class AIInsightsHelper
{
    public static function getPatterns()
    {
        return Cache::get('sentinel:ai:patterns', []);
    }

    public static function getAnomalies()
    {
        return Cache::get('sentinel:ai:anomalies', []);
    }

    public static function getPredictions()
    {
        return Cache::get('sentinel:ai:predictions', []);
    }

    public static function getRecommendations()
    {
        return Cache::get('sentinel:ai:recommendations', []);
    }

    public static function getInsightsSummary()
    {
        return [
            'patterns' => self::getPatterns(),
            'anomalies' => self::getAnomalies(),
            'predictions' => self::getPredictions(),
            'recommendations' => self::getRecommendations(),
            'last_updated' => Cache::get('sentinel:ai:last_updated', now()),
        ];
    }

    public static function hasActiveAnomalies()
    {
        $anomalies = self::getAnomalies();
        return collect($anomalies)->filter()->isNotEmpty();
    }

    public static function hasCriticalRecommendations()
    {
        $recommendations = self::getRecommendations();
        return collect($recommendations)->where('priority', 'critical')->isNotEmpty();
    }

    public static function getHealthScore()
    {
        $predictions = self::getPredictions();
        $anomalies = self::getAnomalies();
        
        $score = 100;

        if (isset($predictions['downtime_risk']['score'])) {
            $score -= $predictions['downtime_risk']['score'];
        }

        $activeAnomalies = collect($anomalies)->filter()->count();
        $score -= min($activeAnomalies * 10, 30);

        return max(0, min(100, $score));
    }

    public static function getHealthStatus()
    {
        $score = self::getHealthScore();

        if ($score >= 80) return 'excellent';
        if ($score >= 60) return 'good';
        if ($score >= 40) return 'fair';
        if ($score >= 20) return 'poor';
        return 'critical';
    }
}
