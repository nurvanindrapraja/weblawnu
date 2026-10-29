<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_admin_dashboard(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_reporter_cannot_access_admin_dashboard(): void
    {
        $this->actingAs(User::factory()->reporter()->create())
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_redaktur_cannot_access_admin_dashboard(): void
    {
        $this->actingAs(User::factory()->redaktur()->create())
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_reporter_can_access_reporter_dashboard(): void
    {
        $this->actingAs(User::factory()->reporter()->create())
            ->get(route('reporter.dashboard'))
            ->assertOk();
    }

    public function test_redaktur_can_access_redaktur_dashboard(): void
    {
        $this->actingAs(User::factory()->redaktur()->create())
            ->get(route('redaktur.dashboard'))
            ->assertOk();
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.dashboard'))
            ->assertOk();
    }
}
