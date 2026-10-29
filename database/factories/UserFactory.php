<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'status' => 'active',
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the user should have the given role.
     */
    public function asRole(string $roleName): static
    {
        return $this->afterCreating(function (User $user) use ($roleName): void {
            $role = Role::where('name', $roleName)->first()
                ?? Role::factory()->create(['name' => $roleName]);
            $user->roles()->attach($role->id);
        });
    }

    /**
     * Indicate that the user has the admin role.
     */
    public function admin(): static
    {
        return $this->asRole('admin');
    }

    /**
     * Indicate that the user has the reporter role.
     */
    public function reporter(): static
    {
        return $this->asRole('reporter');
    }

    /**
     * Indicate that the user has the redaktur role.
     */
    public function redaktur(): static
    {
        return $this->asRole('redaktur');
    }
}
