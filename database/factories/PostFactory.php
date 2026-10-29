<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'author_id' => User::factory(),
            'category_id' => Category::factory(),
            'title' => fake()->unique()->sentence(5),
            'slug' => fn (array $attributes) => Str::slug($attributes['title']).'-'.Str::lower(Str::random(4)),
            'excerpt' => fake()->sentence(15),
            'content' => fake()->paragraphs(3, "\n"),
            'status' => 'draft',
            'views' => 0,
            'rating_average' => 0,
            'rating_count' => 0,
        ];
    }

    /**
     * Indicate that the post has been submitted for review.
     */
    public function submitted(): static
    {
        return $this->state([
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);
    }

    /**
     * Indicate that the post is under review.
     */
    public function inReview(): static
    {
        return $this->state([
            'status' => 'review',
            'submitted_at' => now()->subHour(),
            'reviewed_by' => User::factory(),
        ]);
    }

    /**
     * Indicate that the post has been approved.
     */
    public function approved(): static
    {
        return $this->state([
            'status' => 'approved',
            'submitted_at' => now()->subDays(2),
            'reviewed_by' => User::factory(),
            'reviewed_at' => now()->subDay(),
        ]);
    }

    /**
     * Indicate that the post is published and publicly visible.
     */
    public function published(): static
    {
        return $this->state([
            'status' => 'published',
            'submitted_at' => now()->subDays(3),
            'reviewed_by' => User::factory(),
            'reviewed_at' => now()->subDays(2),
            'published_by' => User::factory(),
            'published_at' => now()->subDay(),
        ]);
    }

    /**
     * Indicate that the post has been rejected.
     */
    public function rejected(string $note = 'Perlu revisi sebelum dipublikasikan.'): static
    {
        return $this->state([
            'status' => 'rejected',
            'submitted_at' => now()->subDay(),
            'reviewed_by' => User::factory(),
            'reviewed_at' => now(),
            'rejection_note' => $note,
        ]);
    }

    /**
     * Indicate that the post has been archived.
     */
    public function archived(): static
    {
        return $this->state([
            'status' => 'archived',
        ]);
    }
}
