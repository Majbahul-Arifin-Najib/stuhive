<?php

namespace Database\Factories;

use App\Models\Poll;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Poll>
 */
class PollFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->student(),
            'question' => fake()->randomElement([
                'Should the library stay open until midnight during finals?',
                'Do we need more microwave ovens in the cafeteria?',
                'Should club events be scheduled on Thursdays?',
                'Is the current class routine convenient for you?',
            ]),
        ];
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'closes_at' => now()->subDay(),
        ]);
    }
}
