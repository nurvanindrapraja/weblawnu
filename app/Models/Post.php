<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'author_id',
        'category_id',
        'featured_media_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'status',
        'rejection_note',
        'submitted_at',
        'reviewed_at',
        'published_at',
        'reviewed_by',
        'published_by',
        'views',
        'rating_average',
        'rating_count',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'published_at' => 'datetime',
        'views' => 'integer',
        'rating_average' => 'decimal:2',
        'rating_count' => 'integer',
    ];

    // Relationships
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function featuredMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_media_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'post_tag');
    }

    public function media(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'post_media')
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    // Scopes
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    public function scopeSubmitted(Builder $query): Builder
    {
        return $query->where('status', 'submitted');
    }

    public function scopeInReview(Builder $query): Builder
    {
        return $query->where('status', 'review');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', 'rejected');
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->where('status', 'archived');
    }

    public function scopePopularityScore(Builder $query): Builder
    {
        return $query->select('*')
            ->selectRaw('views + (rating_average * rating_count) as popularity_score')
            ->orderByDesc('popularity_score');
    }

    // Accessors
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getExcerptTextAttribute(): string
    {
        if ($this->excerpt) {
            return Str::limit(strip_tags($this->excerpt), 200);
        }

        return Str::limit(strip_tags($this->content ?? ''), 200);
    }

    public function isPublished(): bool
    {
        return $this->status === 'published'
            && $this->published_at
            && $this->published_at->isPast();
    }
}
