<?php

namespace Database\Factories;

use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->unique()->sentence(3),
            'slug' => fn (array $attributes) => Str::slug($attributes['title']).'-'.Str::lower(Str::random(4)),
            'content' => fake()->paragraphs(2, "\n"),
            'status' => 'published',
            'published_at' => now(),
        ];
    }

    /**
     * Indicate that the page is not published yet.
     */
    public function unpublished(): static
    {
        return $this->state([
            'status' => 'draft',
            'published_at' => null,
        ]);
    }
}
