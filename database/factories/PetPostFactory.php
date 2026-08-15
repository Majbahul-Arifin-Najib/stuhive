<?php

namespace Database\Factories;

use App\Enums\PostType;
use App\Models\PetPost;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PetPost>
 */
class PetPostFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'post_id' => Post::factory()->ofType(PostType::Pet),
            'pet_name' => fake()->randomElement(['Kitkat', 'Momo', 'Tiger', 'Bhutu', 'Coco']),
            'spotted_at' => fake()->randomElement(['UB1 entrance', 'Cafeteria back gate', 'Library lawn', 'Parking lot']),
        ];
    }
}
