<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Organisasi', 'slug' => 'organisasi', 'description' => 'Berita tentang organisasi LWP PWNU'],
            ['name' => 'Wakaf', 'slug' => 'wakaf', 'description' => 'Berita tentang pengelolaan wakaf'],
            ['name' => 'Pertanahan', 'slug' => 'pertanahan', 'description' => 'Berita tentang pertanahan dan aset'],
            ['name' => 'Kegiatan', 'slug' => 'kegiatan', 'description' => 'Berita tentang kegiatan LWP'],
            ['name' => 'Regulasi', 'slug' => 'regulasi', 'description' => 'Berita tentang regulasi dan kebijakan'],
            ['name' => 'Opini', 'slug' => 'opini', 'description' => 'Opini dan pandangan'],
            ['name' => 'Pengumuman', 'slug' => 'pengumuman', 'description' => 'Pengumuman resmi'],
            ['name' => 'Tokoh', 'slug' => 'tokoh', 'description' => 'Profil dan berita tokoh'],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(['slug' => $cat['slug']], $cat + ['is_active' => true]);
        }
    }
}
