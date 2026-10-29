<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Media extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uploaded_by',
        'filename',
        'original_name',
        'disk',
        'path',
        'mime_type',
        'size',
        'width',
        'height',
        'alt_text',
        'caption',
    ];

    protected $casts = [
        'size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_media')
            ->withPivot('sort_order');
    }

    public function galleries(): BelongsToMany
    {
        return $this->belongsToMany(Gallery::class, 'gallery_images')
            ->withPivot(['caption', 'sort_order']);
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/'.$this->path);
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size ?? 0;

        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2).' MB';
        }

        if ($bytes >= 1024) {
            return round($bytes / 1024, 2).' KB';
        }

        return $bytes.' B';
    }

    public function isImage(): bool
    {
        return in_array($this->mime_type, [
            'image/jpeg',
            'image/png',
            'image/webp',
            'image/gif',
        ]);
    }

    /**
     * Media yang sudah dipakai pada berita berstatus published
     * (melalui post_media maupun featured_media_id).
     */
    public function scopeUsedInPublishedPosts(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->whereHas('posts', fn (Builder $p) => $p->where('status', 'published'))
                ->orWhereIn('id', Post::where('status', 'published')
                    ->whereNotNull('featured_media_id')
                    ->pluck('featured_media_id'));
        });
    }

    public function isUsedInPublishedPosts(): bool
    {
        return static::query()->whereKey($this->id)->usedInPublishedPosts()->exists();
    }
}
