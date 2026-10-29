<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_with_valid_username_redirects_admin_to_admin_dashboard(): void
    {
        $user = User::factory()->admin()->create(['username' => 'adminbjk']);

        $response = $this->post(route('login.post'), [
            'login' => 'adminbjk',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'module' => 'Auth',
            'action' => 'LOGIN',
        ]);
    }

    public function test_login_with_valid_email_redirects_reporter_to_reporter_dashboard(): void
    {
        $user = User::factory()->reporter()->create();

        $response = $this->post(route('login.post'), [
            'login' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('reporter.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_with_invalid_credentials_keeps_user_guest(): void
    {
        User::factory()->admin()->create(['username' => 'adminbjk']);

        $response = $this->from(route('login'))
            ->post(route('login.post'), [
                'login' => 'adminbjk',
                'password' => 'password-salah',
            ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_logout_clears_authentication(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'action' => 'LOGOUT',
        ]);
    }
}
