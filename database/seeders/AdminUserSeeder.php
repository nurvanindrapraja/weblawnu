<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $reporterRole = Role::where('name', 'reporter')->first();
        $redakturRole = Role::where('name', 'redaktur')->first();

        // Admin
        $admin = User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Administrator',
                'email' => 'admin@lwpnujatim.or.id',
                'phone' => '081234567890',
                'password' => 'password',
                'status' => 'active',
            ]
        );
        if ($adminRole) {
            $admin->roles()->sync([$adminRole->id]);
        }

        // Reporter
        $reporter = User::updateOrCreate(
            ['username' => 'reporter'],
            [
                'name' => 'Reporter LWP',
                'email' => 'reporter@lwpnujatim.or.id',
                'phone' => '081234567891',
                'password' => 'password',
                'status' => 'active',
            ]
        );
        if ($reporterRole) {
            $reporter->roles()->sync([$reporterRole->id]);
        }

        // Redaktur
        $redaktur = User::updateOrCreate(
            ['username' => 'redaktur'],
            [
                'name' => 'Redaktur LWP',
                'email' => 'redaktur@lwpnujatim.or.id',
                'phone' => '081234567892',
                'password' => 'password',
                'status' => 'active',
            ]
        );
        if ($redakturRole) {
            $redaktur->roles()->sync([$redakturRole->id]);
        }
    }
}
