<?php

namespace App\Services;

use App\Models\Setting;

class SettingService
{
    public function get(string $key, $default = null)
    {
        return Setting::get($key, $default);
    }

    public function set(string $key, $value, string $type = 'string', string $group = 'general'): void
    {
        Setting::set($key, $value, $type, $group);
    }

    public function all(): array
    {
        return Setting::allSettings();
    }

    public function update(array $data): void
    {
        foreach ($data as $key => $value) {
            if ($key === '_method' || $key === '_token') {
                continue;
            }

            $existing = Setting::where('key', $key)->first();
            $type = $existing?->type ?? 'string';
            $group = $existing?->group_name ?? 'general';

            Setting::set($key, $value, $type, $group);
        }
    }
}
