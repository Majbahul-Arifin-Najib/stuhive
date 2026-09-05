<?php

namespace Database\Factories;

use App\Enums\PostType;
use App\Models\CandidPost;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CandidPost>
 */
class CandidPostFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'post_id' => Post::factory()->ofType(PostType::Candid),
            'image_path' => 'candid/'.fake()->uuid().'.jpg',
        ];
    }
}
