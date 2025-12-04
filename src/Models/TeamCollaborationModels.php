<?php

namespace PicoBaz\Sentinel\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $table = 'sentinel_teams';

    protected $fillable = [
        'name',
        'description',
        'slug',
    ];

    public function members()
    {
        return $this->hasMany(TeamMember::class, 'team_id');
    }

    public function responsibilities()
    {
        return $this->hasMany(TeamResponsibility::class, 'team_id');
    }

    public function issues()
    {
        return $this->hasMany(\PicoBaz\Sentinel\Modules\TeamCollaboration\IssueTracker::class, 'team_id');
    }
}

class TeamMember extends Model
{
    protected $table = 'sentinel_team_members';

    protected $fillable = [
        'team_id',
        'user_id',
        'role',
        'points',
        'badges',
        'active',
        'notification_preferences',
        'notification_channels',
    ];

    protected $casts = [
        'active' => 'boolean',
        'badges' => 'array',
        'notification_preferences' => 'array',
        'notification_channels' => 'array',
        'points' => 'integer',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function hasBadge($badge)
    {
        return in_array($badge, $this->badges ?? []);
    }

    public function awardBadge($badge)
    {
        $badges = $this->badges ?? [];
        
        if (!in_array($badge, $badges)) {
            $badges[] = $badge;
            $this->update(['badges' => $badges]);
        }
    }
}

class TeamResponsibility extends Model
{
    protected $table = 'sentinel_team_responsibilities';

    protected $fillable = [
        'team_id',
        'log_type',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class, 'team_id');
    }
}

class TeamNotification extends Model
{
    protected $table = 'sentinel_team_notifications';

    protected $fillable = [
        'user_id',
        'log_id',
        'issue_id',
        'type',
        'title',
        'message',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function log()
    {
        return $this->belongsTo(SentinelLog::class, 'log_id');
    }

    public function issue()
    {
        return $this->belongsTo(\PicoBaz\Sentinel\Modules\TeamCollaboration\IssueTracker::class, 'issue_id');
    }

    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }
}

class IssueComment extends Model
{
    protected $table = 'sentinel_issue_comments';

    protected $fillable = [
        'issue_id',
        'user_id',
        'comment',
    ];

    public function issue()
    {
        return $this->belongsTo(\PicoBaz\Sentinel\Modules\TeamCollaboration\IssueTracker::class, 'issue_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
