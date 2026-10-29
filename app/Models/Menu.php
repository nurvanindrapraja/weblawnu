<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    protected $fillable = [
        'parent_id',
        'title',
        'type',
        'url',
        'target',
        'icon',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Menu::class, 'parent_id')
            ->orderBy('sort_order');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id')->orderBy('sort_order');
    }

    public function getResolvedUrlAttribute(): string
    {
        if ($this->url) {
            return $this->url;
        }

        return match ($this->type) {
            'page' => '/halaman/'.($this->url ?? ''),
            'post' => '/berita/'.($this->url ?? ''),
            'category' => '/kategori/'.($this->url ?? ''),
            default => '/',
        };
    }
}
