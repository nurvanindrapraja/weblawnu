<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_index_renders_live_filters_without_filter_button(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['name' => 'Reporter Uji']);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('id="user-search"', false)
            ->assertSee('id="user-status-filter"', false)
            ->assertSee('id="users-rows"', false)
            ->assertSee('id="users-pagination"', false)
            ->assertDontSee('>Cari<', false);
    }

    public function test_users_filter_returns_filtered_rows_as_json(): void
    {
        $admin = User::factory()->admin()->create();
        $match = User::factory()->create([
            'name' => 'Ahmad Fauzi',
            'status' => 'active',
        ]);
        User::factory()->create([
            'name' => 'Budi Santoso',
            'status' => 'inactive',
        ]);

        $response = $this->actingAs($admin)
            ->getJson(route('admin.users.filter', [
                'search' => 'Ahmad',
                'status' => 'active',
            ]));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('page', 1)
            ->assertJsonPath('total', 1);

        $this->assertStringContainsString('Ahmad Fauzi', $response->json('rows'));
        $this->assertStringNotContainsString('Budi Santoso', $response->json('rows'));
    }

    public function test_users_filter_requires_admin(): void
    {
        $reporter = User::factory()->reporter()->create();

        $this->actingAs($reporter)
            ->getJson(route('admin.users.filter'))
            ->assertForbidden();
    }
}
