<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Dashboard
            ['name' => 'dashboard.view', 'display_name' => 'Lihat Dashboard'],
            // Users
            ['name' => 'users.view', 'display_name' => 'Lihat Pengguna'],
            ['name' => 'users.create', 'display_name' => 'Tambah Pengguna'],
            ['name' => 'users.update', 'display_name' => 'Edit Pengguna'],
            ['name' => 'users.delete', 'display_name' => 'Hapus Pengguna'],
            // Roles
            ['name' => 'roles.view', 'display_name' => 'Lihat Role'],
            ['name' => 'roles.create', 'display_name' => 'Tambah Role'],
            ['name' => 'roles.update', 'display_name' => 'Edit Role'],
            ['name' => 'roles.delete', 'display_name' => 'Hapus Role'],
            // Menus
            ['name' => 'menus.view', 'display_name' => 'Lihat Menu'],
            ['name' => 'menus.create', 'display_name' => 'Tambah Menu'],
            ['name' => 'menus.update', 'display_name' => 'Edit Menu'],
            ['name' => 'menus.delete', 'display_name' => 'Hapus Menu'],
            // Pages
            ['name' => 'pages.view', 'display_name' => 'Lihat Halaman'],
            ['name' => 'pages.create', 'display_name' => 'Tambah Halaman'],
            ['name' => 'pages.update', 'display_name' => 'Edit Halaman'],
            ['name' => 'pages.delete', 'display_name' => 'Hapus Halaman'],
            ['name' => 'pages.publish', 'display_name' => 'Publish Halaman'],
            // Posts
            ['name' => 'posts.view', 'display_name' => 'Lihat Berita'],
            ['name' => 'posts.create', 'display_name' => 'Buat Berita'],
            ['name' => 'posts.update', 'display_name' => 'Edit Berita'],
            ['name' => 'posts.delete', 'display_name' => 'Hapus Berita'],
            ['name' => 'posts.submit', 'display_name' => 'Submit Berita'],
            ['name' => 'posts.review', 'display_name' => 'Review Berita'],
            ['name' => 'posts.approve', 'display_name' => 'Approve Berita'],
            ['name' => 'posts.reject', 'display_name' => 'Reject Berita'],
            ['name' => 'posts.publish', 'display_name' => 'Publish Berita'],
            ['name' => 'posts.unpublish', 'display_name' => 'Unpublish Berita'],
            // Categories
            ['name' => 'categories.view', 'display_name' => 'Lihat Kategori'],
            ['name' => 'categories.create', 'display_name' => 'Tambah Kategori'],
            ['name' => 'categories.update', 'display_name' => 'Edit Kategori'],
            ['name' => 'categories.delete', 'display_name' => 'Hapus Kategori'],
            // Tags
            ['name' => 'tags.view', 'display_name' => 'Lihat Tag'],
            ['name' => 'tags.create', 'display_name' => 'Tambah Tag'],
            ['name' => 'tags.update', 'display_name' => 'Edit Tag'],
            ['name' => 'tags.delete', 'display_name' => 'Hapus Tag'],
            // Media
            ['name' => 'media.view', 'display_name' => 'Lihat Media'],
            ['name' => 'media.upload', 'display_name' => 'Upload Media'],
            ['name' => 'media.update', 'display_name' => 'Edit Media'],
            ['name' => 'media.delete', 'display_name' => 'Hapus Media'],
            // Ratings
            ['name' => 'ratings.view', 'display_name' => 'Lihat Rating'],
            ['name' => 'ratings.delete', 'display_name' => 'Hapus Rating'],
            // Contact
            ['name' => 'contact.view', 'display_name' => 'Lihat Pesan Kontak'],
            ['name' => 'contact.update', 'display_name' => 'Update Pesan Kontak'],
            ['name' => 'contact.delete', 'display_name' => 'Hapus Pesan Kontak'],
            // Settings
            ['name' => 'settings.view', 'display_name' => 'Lihat Pengaturan'],
            ['name' => 'settings.update', 'display_name' => 'Update Pengaturan'],
            // Activity Logs
            ['name' => 'activity_logs.view', 'display_name' => 'Lihat Activity Log'],
        ];

        foreach ($permissions as $perm) {
            Permission::updateOrCreate(['name' => $perm['name']], $perm);
        }
    }
}
