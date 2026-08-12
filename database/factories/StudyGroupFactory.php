<?php

namespace Database\Factories;

use App\Enums\PostType;
use App\Models\Post;
use App\Models\StudyGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudyGroup>
 */
class StudyGroupFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'post_id' => Post::factory()->ofType(PostType::StudyGroup),
            'course_name' => fake()->randomElement(['CSE220', 'CSE370', 'MAT216', 'PHY111']),
            'max_members' => fake()->numberBetween(3, 6),
        ];
    }

    public function withOpenChat(): static
    {
        return $this->state(fn (array $attributes) => [
            'chat_expires_at' => now()->addHours(24),
        ]);
    }

    public function withExpiredChat(): static
    {
        return $this->state(fn (array $attributes) => [
            'chat_expires_at' => now()->subHour(),
        ]);
    }
}
