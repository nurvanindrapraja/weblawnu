<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Admin gets ALL permissions
        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $allPermissions = Permission::all()->pluck('id');
            $admin->permissions()->sync($allPermissions);
        }

        // Reporter permissions
        $reporter = Role::where('name', 'reporter')->first();
        if ($reporter) {
            $reporterPerms = [
                'dashboard.view',
                'posts.view',
                'posts.create',
                'posts.update',
                'posts.delete',
                'posts.submit',
                'media.view',
                'media.upload',
                'media.update',
                'media.delete',
                'categories.view',
                'tags.view',
            ];
            $ids = Permission::whereIn('name', $reporterPerms)->pluck('id');
            $reporter->permissions()->sync($ids);
        }

        // Redaktur permissions
        $redaktur = Role::where('name', 'redaktur')->first();
        if ($redaktur) {
            $redakturPerms = [
                'dashboard.view',
                'posts.view',
                'posts.create',
                'posts.update',
                'posts.delete',
                'posts.submit',
                'posts.review',
                'posts.approve',
                'posts.reject',
                'posts.publish',
                'posts.unpublish',
                'categories.view',
                'categories.create',
                'categories.update',
                'tags.view',
                'tags.create',
                'tags.update',
                'media.view',
                'media.upload',
                'media.update',
                'media.delete',
                'ratings.view',
                'contact.view',
                'contact.update',
                'contact.delete',
            ];
            $ids = Permission::whereIn('name', $redakturPerms)->pluck('id');
            $redaktur->permissions()->sync($ids);
        }
    }
}
