<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminActivityLogFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_logs_index_renders_live_filters_without_filter_button(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.logs.index'))
            ->assertOk()
            ->assertSee('id="log-module-filter"', false)
            ->assertSee('id="log-search"', false)
            ->assertSee('id="logs-rows"', false)
            ->assertSee('id="logs-pagination"', false)
            ->assertDontSee('>Filter<', false);
    }

    public function test_logs_index_module_options_match_actual_log_modules(): void
    {
        $admin = User::factory()->admin()->create();

        ActivityLog::create([
            'user_id' => $admin->id,
            'module' => 'Users',
            'action' => 'CREATE',
            'description' => 'Aktivitas pengguna',
            'created_at' => now(),
        ]);
        ActivityLog::create([
            'user_id' => $admin->id,
            'module' => 'Posts',
            'action' => 'CREATE',
            'description' => 'Aktivitas berita',
            'created_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.logs.index'))
            ->assertOk()
            ->assertSee('value="Users"', false)
            ->assertSee('value="Posts"', false)
            ->assertDontSee('value="user"', false)
            ->assertDontSee('value="post"', false);
    }

    public function test_logs_filter_returns_filtered_rows_as_json(): void
    {
        $admin = User::factory()->admin()->create();

        ActivityLog::create([
            'user_id' => $admin->id,
            'module' => 'Users',
            'action' => 'CREATE',
            'description' => "User 'Ahmad Fauzi' dibuat",
            'created_at' => now(),
        ]);
        ActivityLog::create([
            'user_id' => $admin->id,
            'module' => 'Posts',
            'action' => 'PUBLISH',
            'description' => 'Berita publikasi rutin',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->getJson(route('admin.logs.filter', ['search' => 'dibuat']));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('page', 1)
            ->assertJsonPath('total', 1);

        $this->assertStringContainsString('Ahmad Fauzi', $response->json('rows'));
        $this->assertStringNotContainsString('Berita publikasi rutin', $response->json('rows'));
    }

    public function test_logs_filter_filters_by_module(): void
    {
        $admin = User::factory()->admin()->create();

        ActivityLog::create([
            'user_id' => $admin->id,
            'module' => 'Users',
            'action' => 'CREATE',
            'description' => 'Aktivitas pengguna',
            'created_at' => now(),
        ]);
        ActivityLog::create([
            'user_id' => $admin->id,
            'module' => 'Posts',
            'action' => 'CREATE',
            'description' => 'Aktivitas berita',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->getJson(route('admin.logs.filter', ['module' => 'Users']));

        $response->assertOk()
            ->assertJsonPath('total', 1);

        $this->assertStringContainsString('Aktivitas pengguna', $response->json('rows'));
        $this->assertStringNotContainsString('Aktivitas berita', $response->json('rows'));
    }
}
