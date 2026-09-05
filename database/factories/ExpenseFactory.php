<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Expense>
 */
class ExpenseFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->student(),
            'spent_on' => fake()->randomElement(['Lunch at cafeteria', 'Rickshaw fare', 'Photocopy', 'Lab manual', 'Coffee', 'Bus pass']),
            'category' => fake()->randomElement(Expense::CATEGORIES),
            'amount' => fake()->numberBetween(30, 1500),
            'spent_at' => fake()->dateTimeBetween('first day of this month', 'today')->format('Y-m-d'),
        ];
    }
}
