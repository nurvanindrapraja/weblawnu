<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rating extends Model
{
    protected $fillable = [
        'post_id',
        'rating',
        'visitor_hash',
        'session_hash',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
