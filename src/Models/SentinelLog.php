<?php

namespace PicoBaz\Sentinel\Models;

use Illuminate\Database\Eloquent\Model;

class SentinelLog extends Model
{
    protected $fillable = [
        'type',
        'data',
        'severity',
        'created_at',
    ];

    protected $casts = [
        'data' => 'array',
        'created_at' => 'datetime',
    ];

    public $timestamps = false;
}
