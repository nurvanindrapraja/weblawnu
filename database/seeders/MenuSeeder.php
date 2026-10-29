<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $menus = [
            ['title' => 'HOME', 'type' => 'custom', 'url' => '/', 'sort_order' => 1, 'is_active' => true],
            ['title' => 'PROFIL', 'type' => 'dropdown', 'url' => null, 'sort_order' => 2, 'is_active' => true],
            ['title' => 'BERITA', 'type' => 'dropdown', 'url' => '/berita', 'sort_order' => 3, 'is_active' => true],
            ['title' => 'GALERI', 'type' => 'custom', 'url' => '/galeri', 'sort_order' => 4, 'is_active' => true],
            ['title' => 'DOKUMEN', 'type' => 'category', 'url' => '/kategori/dokumen', 'sort_order' => 5, 'is_active' => true],
            ['title' => 'KONTAK KAMI', 'type' => 'custom', 'url' => '/kontak', 'sort_order' => 6, 'is_active' => true],
        ];

        foreach ($menus as $menu) {
            Menu::updateOrCreate(['title' => $menu['title'], 'parent_id' => null], $menu);
        }

        // Top-level items that were moved into submenus.
        Menu::whereNull('parent_id')
            ->whereIn('title', ['WAKAF', 'PERTANAHAN', 'PROGRAM'])
            ->update(['is_active' => false]);

        $profil = Menu::where('title', 'PROFIL')->whereNull('parent_id')->first();
        if ($profil) {
            // Rename the legacy "Tentang LWP" submenu before upserting.
            Menu::where('parent_id', $profil->id)
                ->where('title', 'Tentang LWP')
                ->update(['title' => 'Tentang LAWNU', 'url' => '/halaman/tentang-lawnu']);

            $profilSubmenus = [
                ['title' => 'Tentang LAWNU', 'type' => 'page', 'url' => '/halaman/tentang-lawnu', 'parent_id' => $profil->id, 'sort_order' => 1, 'is_active' => true],
                ['title' => 'Sejarah', 'type' => 'page', 'url' => '/halaman/sejarah', 'parent_id' => $profil->id, 'sort_order' => 2, 'is_active' => true],
                ['title' => 'Visi & Misi', 'type' => 'page', 'url' => '/halaman/visi-misi', 'parent_id' => $profil->id, 'sort_order' => 3, 'is_active' => true],
                ['title' => 'Struktur Organisasi', 'type' => 'page', 'url' => '/halaman/struktur-organisasi', 'parent_id' => $profil->id, 'sort_order' => 4, 'is_active' => true],
                ['title' => 'Program', 'type' => 'page', 'url' => '/halaman/program', 'parent_id' => $profil->id, 'sort_order' => 5, 'is_active' => true],
            ];

            foreach ($profilSubmenus as $sub) {
                Menu::updateOrCreate(['title' => $sub['title'], 'parent_id' => $sub['parent_id']], $sub);
            }
        }

        $berita = Menu::where('title', 'BERITA')->whereNull('parent_id')->first();
        if ($berita) {
            $beritaSubmenus = [
                ['title' => 'Wakaf', 'type' => 'category', 'url' => '/kategori/wakaf', 'parent_id' => $berita->id, 'sort_order' => 1, 'is_active' => true],
                ['title' => 'Pertanahan', 'type' => 'category', 'url' => '/kategori/pertanahan', 'parent_id' => $berita->id, 'sort_order' => 2, 'is_active' => true],
                ['title' => 'Opini', 'type' => 'category', 'url' => '/kategori/opini', 'parent_id' => $berita->id, 'sort_order' => 3, 'is_active' => true],
                ['title' => 'Regulasi', 'type' => 'category', 'url' => '/kategori/regulasi', 'parent_id' => $berita->id, 'sort_order' => 4, 'is_active' => true],
                ['title' => 'Tokoh', 'type' => 'category', 'url' => '/kategori/tokoh', 'parent_id' => $berita->id, 'sort_order' => 5, 'is_active' => true],
                ['title' => 'Tanya Jawab', 'type' => 'category', 'url' => '/kategori/tanya-jawab', 'parent_id' => $berita->id, 'sort_order' => 6, 'is_active' => true],
                ['title' => 'Lain-lain', 'type' => 'custom', 'url' => '/berita', 'parent_id' => $berita->id, 'sort_order' => 7, 'is_active' => true],
            ];

            foreach ($beritaSubmenus as $sub) {
                Menu::updateOrCreate(['title' => $sub['title'], 'parent_id' => $sub['parent_id']], $sub);
            }
        }
    }
}
