<?php

namespace Database\Factories;

use App\Enums\PostType;
use App\Models\LostFoundPost;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LostFoundPost>
 */
class LostFoundPostFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'post_id' => Post::factory()->ofType(PostType::LostFound),
            'item_name' => fake()->randomElement(['Blue water bottle', 'Student ID card', 'Casio calculator', 'Black umbrella', 'AirPods case']),
            'location' => fake()->randomElement(['UB4 cafeteria', 'Library 3rd floor', 'Auditorium lobby', 'Bus stop gate 2']),
            'is_found' => false,
        ];
    }

    public function found(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_found' => true,
            'found_at' => now(),
        ]);
    }
}
