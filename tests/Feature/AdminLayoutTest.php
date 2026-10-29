<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sidebar_shows_white_nu_logo(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('images/logo_nu_putih.png', false);
    }

    public function test_logout_asks_confirmation_before_submitting_form(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('id="logout-form"', false)
            ->assertSee('@click="open = false; confirmLogout()"', false)
            ->assertSee('function confirmLogout', false);
    }
}
