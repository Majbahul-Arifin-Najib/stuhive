<?php

namespace Database\Factories;

use App\Enums\PostType;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->student(),
            'type' => PostType::LostFound,
            'content' => fake()->paragraph(),
        ];
    }

    public function ofType(PostType $type): static
    {
        return $this->state(fn (array $attributes) => ['type' => $type]);
    }
}
