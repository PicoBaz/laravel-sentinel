<?php

namespace PicoBaz\Sentinel\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityBlacklist extends Model
{
    protected $table = 'sentinel_security_blacklist';

    protected $fillable = [
        'ip',
        'reason',
        'threat_count',
        'security_score',
        'threat_level',
        'auto_blocked',
        'blocked_at',
        'last_threat_at',
    ];

    protected $casts = [
        'auto_blocked' => 'boolean',
        'blocked_at' => 'datetime',
        'last_threat_at' => 'datetime',
    ];

    public function incrementThreat()
    {
        $this->increment('threat_count');
        $this->update([
            'last_threat_at' => now(),
            'security_score' => max(0, $this->security_score - 10),
        ]);

        $this->updateThreatLevel();
    }

    protected function updateThreatLevel()
    {
        $score = $this->security_score;

        if ($score >= 80) {
            $level = 'low';
        } elseif ($score >= 50) {
            $level = 'medium';
        } elseif ($score >= 20) {
            $level = 'high';
        } else {
            $level = 'critical';
        }

        $this->update(['threat_level' => $level]);
    }

    public function scopeActive($query)
    {
        return $query->whereNotNull('blocked_at');
    }

    public function scopeCritical($query)
    {
        return $query->where('threat_level', 'critical');
    }

    public function scopeAutoBlocked($query)
    {
        return $query->where('auto_blocked', true);
    }
}
