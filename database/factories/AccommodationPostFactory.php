<?php

namespace Database\Factories;

use App\Enums\PostType;
use App\Models\AccommodationPost;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AccommodationPost>
 */
class AccommodationPostFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'post_id' => Post::factory()->ofType(PostType::Accommodation),
            'area' => fake()->randomElement(['Merul Badda', 'Bashundhara R/A', 'Mohakhali', 'Rampura', 'Banasree']),
            'walking_distance' => fake()->randomElement(['5 min', '10 min', '15 min', '25 min']),
            'phone_number' => '01'.fake()->numerify('#########'),
            'rent' => fake()->numberBetween(4000, 20000),
        ];
    }
}
