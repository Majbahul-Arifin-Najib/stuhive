<?php

namespace Database\Factories;

use App\Enums\PostType;
use App\Models\MarketplacePost;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MarketplacePost>
 */
class MarketplacePostFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'post_id' => Post::factory()->ofType(PostType::Marketplace),
            'product_name' => fake()->randomElement(['Drafting table', 'Scientific calculator', 'Cycle', 'Guitar', 'Lab coat']),
            'price' => fake()->numberBetween(300, 15000),
            'condition' => fake()->randomElement(['new', 'like_new', 'used']),
            'contact_number' => '01'.fake()->numerify('#########'),
        ];
    }
}
