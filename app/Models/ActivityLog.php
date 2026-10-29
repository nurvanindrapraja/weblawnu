<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'module',
        'action',
        'record_id',
        'description',
        'ip_hash',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    public function scopeRecent($query, int $limit = 50)
    {
        return $query->orderByDesc('created_at')->limit($limit);
    }
}
