<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'site_name', 'value' => 'LAWNU JATIM', 'type' => 'string', 'group_name' => 'general'],
            ['key' => 'site_description', 'value' => 'Website resmi Lembaga Aset dan Wakaf Pengurus Wilayah Nahdlatul Ulama Jawa Timur', 'type' => 'string', 'group_name' => 'general'],
            ['key' => 'site_email', 'value' => 'admin@lwpnujatim.com', 'type' => 'string', 'group_name' => 'general'],
            ['key' => 'site_phone', 'value' => '081330278721', 'type' => 'string', 'group_name' => 'general'],
            ['key' => 'site_address', 'value' => 'Jl Masjid Al Akbar Timur 9, Kota Surabaya, 602333', 'type' => 'string', 'group_name' => 'general'],
            ['key' => 'facebook_url', 'value' => '', 'type' => 'string', 'group_name' => 'social'],
            ['key' => 'instagram_url', 'value' => 'https://www.instagram.com/pwlwpnujatim/', 'type' => 'string', 'group_name' => 'social'],
            ['key' => 'youtube_url', 'value' => 'https://www.youtube.com/@lwpnujatim3460', 'type' => 'string', 'group_name' => 'social'],
            ['key' => 'whatsapp_number', 'value' => '081330278721', 'type' => 'string', 'group_name' => 'social'],
            ['key' => 'google_maps_url', 'value' => '', 'type' => 'string', 'group_name' => 'general'],
            ['key' => 'posts_per_page', 'value' => '9', 'type' => 'integer', 'group_name' => 'display'],
            ['key' => 'site_logo', 'value' => '', 'type' => 'string', 'group_name' => 'general'],
            ['key' => 'site_favicon', 'value' => '', 'type' => 'string', 'group_name' => 'general'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }

        Cache::forget('settings_all');
    }
}
