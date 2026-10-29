<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'username',
        'email',
        'phone',
        'password',
        'avatar',
        'status',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'last_login_at' => 'datetime',
        'status' => 'string',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'author_id');
    }

    public function galleries(): HasMany
    {
        return $this->hasMany(Gallery::class, 'author_id');
    }

    public function reviewedPosts(): HasMany
    {
        return $this->hasMany(Post::class, 'reviewed_by');
    }

    public function publishedPosts(): HasMany
    {
        return $this->hasMany(Post::class, 'published_by');
    }

    public function pages(): HasMany
    {
        return $this->hasMany(Page::class, 'author_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(Media::class, 'uploaded_by');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'user_id');
    }

    public function hasRole(string $roleName): bool
    {
        return $this->roles->contains('name', $roleName);
    }

    public function hasPermission(string $permissionName): bool
    {
        if ($this->hasRole('admin')) {
            return true;
        }

        return $this->roles->flatMap(fn ($role) => $role->permissions)
            ->contains('name', $permissionName);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isReporter(): bool
    {
        return $this->hasRole('reporter');
    }

    public function isRedaktur(): bool
    {
        return $this->hasRole('redaktur');
    }

    public function getRoleNameAttribute(): string
    {
        return $this->roles->first()->name ?? 'none';
    }
}
