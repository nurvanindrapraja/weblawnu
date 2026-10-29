<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'Pengelola utama dengan akses penuh',
            ],
            [
                'name' => 'reporter',
                'display_name' => 'Reporter',
                'description' => 'Pembuat dan pengelola berita',
            ],
            [
                'name' => 'redaktur',
                'display_name' => 'Redaktur',
                'description' => 'Pemeriksa dan penerbit berita',
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['name' => $role['name']], $role);
        }
    }
}
