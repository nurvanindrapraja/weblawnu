<?php

namespace Database\Factories;

use App\Models\Registration;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Registration>
 */
class RegistrationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->name();

        return [
            'name' => $name,
            'email' => fake()->unique()->safeEmail(),
            'username' => Str::slug($name).fake()->unique()->numberBetween(10, 999),
            'phone' => fake()->optional()->phoneNumber(),
            'password' => bcrypt('password'),
            'role' => 'reporter',
            'reason' => fake()->sentence(10),
            'status' => 'pending',
        ];
    }

    public function redaktur(): static
    {
        return $this->state(['role' => 'redaktur']);
    }

    public function approved(): static
    {
        return $this->state([
            'status' => 'approved',
            'verified_at' => now(),
        ]);
    }
}
