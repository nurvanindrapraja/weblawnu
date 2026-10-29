<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group_name',
    ];

    public static function get(string $key, $default = null)
    {
        $all = Cache::remember('settings_all', 3600, function () {
            return static::query()->pluck('value', 'key')->toArray();
        });

        return $all[$key] ?? $default;
    }

    public static function set(string $key, $value, string $type = 'string', string $group = 'general')
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type, 'group_name' => $group]
        );

        Cache::forget('settings_all');
    }

    public static function allSettings(): array
    {
        return Cache::remember('settings_all', 3600, function () {
            return static::query()->pluck('value', 'key')->toArray();
        });
    }

    public static function clearCache()
    {
        Cache::forget('settings_all');
    }
}
