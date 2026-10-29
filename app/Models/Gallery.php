<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Gallery extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'author_id',
        'title',
        'slug',
        'description',
        'event_date',
        'status',
        'rejection_note',
        'submitted_at',
        'reviewed_at',
        'published_at',
        'reviewed_by',
        'published_by',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'event_date' => 'date',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function images(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'gallery_images')
            ->withPivot(['caption', 'sort_order'])
            ->orderByPivot('sort_order');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeSubmitted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SUBMITTED);
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function firstImage(): ?Media
    {
        return $this->images->first();
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED
            && $this->published_at
            && $this->published_at->isPast();
    }

    public function isPending(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_SUBMITTED], true);
    }
}
