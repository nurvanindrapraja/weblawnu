<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Registration extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    /**
     * Role yang boleh didaftarkan lewat halaman pendaftaran publik.
     *
     * @var string[]
     */
    public const ALLOWED_ROLES = ['reporter', 'redaktur'];

    protected $fillable = [
        'name',
        'email',
        'username',
        'phone',
        'password',
        'role',
        'reason',
        'status',
        'admin_note',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    protected $hidden = [
        'password',
    ];

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }
}
