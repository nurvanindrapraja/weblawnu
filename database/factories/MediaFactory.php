<?php

namespace Database\Factories;

use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Media>
 */
class MediaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $uuid = fake()->unique()->uuid();

        return [
            'filename' => $uuid.'.jpg',
            'original_name' => fake()->word().'.jpg',
            'disk' => 'public',
            'path' => 'posts/'.$uuid.'.jpg',
            'mime_type' => 'image/jpeg',
            'size' => fake()->numberBetween(10_000, 1_000_000),
            'width' => 1280,
            'height' => 720,
            'alt_text' => fake()->sentence(),
        ];
    }
}
