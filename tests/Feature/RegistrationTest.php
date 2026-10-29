<?php

namespace Tests\Feature;

use App\Models\Registration;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_registration_page(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Daftar Akun')
            ->assertSee('value="reporter"', false)
            ->assertSee('value="redaktur"', false);
    }

    public function test_guest_can_register_as_reporter(): void
    {
        $response = $this->post(route('register.post'), [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'phone' => '081234567890',
            'username' => 'budi_santoso',
            'role' => 'reporter',
            'reason' => 'Jurnalis lokal yang ingin berkontribusi.',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('register.success'));

        $this->assertDatabaseHas('registrations', [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'username' => 'budi_santoso',
            'role' => 'reporter',
            'status' => 'pending',
        ]);
        $this->assertNotEquals('password123', Registration::first()->password); // di-hash
    }

    public function test_guest_can_register_as_redaktur(): void
    {
        $this->post(route('register.post'), [
            'name' => 'Siti Aminah',
            'email' => 'siti@example.com',
            'username' => 'siti_aminah',
            'role' => 'redaktur',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('register.success'));

        $this->assertDatabaseHas('registrations', [
            'username' => 'siti_aminah',
            'role' => 'redaktur',
            'status' => 'pending',
        ]);
    }

    public function test_registration_rejects_duplicate_and_invalid_data(): void
    {
        User::factory()->create(['email' => 'taken@example.com', 'username' => 'taken_user']);

        $this->post(route('register.post'), [
            'name' => 'Coba Duplikat',
            'email' => 'taken@example.com',
            'username' => 'taken_user',
            'role' => 'admin', // role tidak diizinkan
            'password' => 'abc', // terlalu pendek
            'password_confirmation' => 'abc',
        ])
            ->assertSessionHasErrors(['email', 'username', 'role', 'password']);

        $this->assertDatabaseCount('registrations', 0);
    }

    public function test_admin_can_approve_registration_and_account_can_login(): void
    {
        $admin = User::factory()->admin()->create();
        Role::factory()->create(['name' => 'reporter']);
        $reg = Registration::factory()->create([
            'username' => 'pendaftar_baru',
            'password' => bcrypt('rahasia123'),
        ]);

        $this->actingAs($admin)
            ->postJson(route('admin.registrations.approve', $reg))
            ->assertOk()
            ->assertJsonPath('success', true);

        $user = User::where('username', 'pendaftar_baru')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('reporter'));
        $this->assertTrue(Hash::check('rahasia123', $user->password));

        $this->assertSame('approved', $reg->fresh()->status);
        $this->assertSame($admin->id, $reg->fresh()->verified_by);
        $this->assertNotNull($reg->fresh()->verified_at);

        // Akun yang baru dibuat langsung bisa login.
        $this->post('/login', [
            'login' => 'pendaftar_baru',
            'password' => 'rahasia123',
        ])->assertRedirect(route('reporter.dashboard'));
    }

    public function test_approval_fails_when_user_already_exists(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->reporter()->create(['email' => 'saja@example.com', 'username' => 'saja']);
        $reg = Registration::factory()->create([
            'email' => 'saja@example.com',
            'username' => 'saja_dua',
        ]);

        $this->actingAs($admin)
            ->postJson(route('admin.registrations.approve', $reg))
            ->assertStatus(400)
            ->assertJsonPath('success', false);

        $this->assertSame('pending', $reg->fresh()->status);
        $this->assertDatabaseCount('users', 2); // tidak ada user baru
    }

    public function test_admin_can_reject_registration_with_note(): void
    {
        $admin = User::factory()->admin()->create();
        $reg = Registration::factory()->create();

        $this->actingAs($admin)
            ->postJson(route('admin.registrations.reject', $reg), [
                'admin_note' => 'Email tidak valid / tidak aktif.',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSame('rejected', $reg->fresh()->status);
        $this->assertSame('Email tidak valid / tidak aktif.', $reg->fresh()->admin_note);
        $this->assertSame($admin->id, $reg->fresh()->verified_by);
    }

    public function test_reject_requires_note(): void
    {
        $admin = User::factory()->admin()->create();
        $reg = Registration::factory()->create();

        $this->actingAs($admin)
            ->postJson(route('admin.registrations.reject', $reg), ['admin_note' => ''])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('admin_note');

        $this->assertSame('pending', $reg->fresh()->status);
    }

    public function test_approving_twice_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        Role::factory()->create(['name' => 'reporter']);
        $reg = Registration::factory()->create();

        $this->actingAs($admin)->postJson(route('admin.registrations.approve', $reg))->assertOk();
        $this->actingAs($admin)
            ->postJson(route('admin.registrations.approve', $reg))
            ->assertStatus(400)
            ->assertJsonPath('success', false);

        $this->assertSame(1, User::where('username', $reg->username)->count());
    }

    public function test_non_admin_cannot_access_registrations(): void
    {
        $reporter = User::factory()->reporter()->create();

        $this->actingAs($reporter)
            ->get(route('admin.registrations.index'))
            ->assertForbidden();
    }

    public function test_admin_registration_page_renders(): void
    {
        $admin = User::factory()->admin()->create();
        Registration::factory()->create(['name' => 'Pendaftar Uji']);

        $this->actingAs($admin)
            ->get(route('admin.registrations.index'))
            ->assertOk()
            ->assertSee('Pendaftar Uji')
            ->assertSee('Menunggu');
    }
}
