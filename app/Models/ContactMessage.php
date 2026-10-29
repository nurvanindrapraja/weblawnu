<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactMessage extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'contact_messages';

    protected $fillable = [
        'ticket_number',
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'status',
        'admin_note',
        'reply',
        'replied_at',
        'handled_by',
        'handled_at',
    ];

    protected $casts = [
        'handled_at' => 'datetime',
        'replied_at' => 'datetime',
    ];

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }

    public function scopeUnresolved($query)
    {
        return $query->whereNotIn('status', ['resolved', 'archived']);
    }
}
