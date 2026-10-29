<?php

namespace Database\Factories;

use App\Models\Gallery;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Gallery>
 */
class GalleryFactory extends Factory
{
    protected $model = Gallery::class;

    public function definition(): array
    {
        return [
            'author_id' => User::factory(),
            'title' => fake()->sentence(4),
            'slug' => Str::slug(fake()->unique()->sentence(4)).'-'.fake()->unique()->numberBetween(1, 99999),
            'description' => fake()->paragraph(),
            'event_date' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'status' => Gallery::STATUS_DRAFT,
        ];
    }

    public function submitted(): static
    {
        return $this->state(fn () => [
            'status' => Gallery::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status' => Gallery::STATUS_PUBLISHED,
            'published_at' => now()->subHour(),
        ]);
    }

    public function rejected(?string $note = null): static
    {
        return $this->state(fn () => [
            'status' => Gallery::STATUS_REJECTED,
            'reviewed_at' => now(),
            'rejection_note' => $note ?? fake()->sentence(),
        ]);
    }
}
