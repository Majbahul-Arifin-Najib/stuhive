<?php

namespace Database\Factories;

use App\Models\Faculty;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Faculty>
 */
class FacultyFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'initial' => Str::upper(fake()->unique()->lexify('???')),
            'designation' => fake()->randomElement(['Lecturer', 'Senior Lecturer', 'Assistant Professor', 'Professor']),
            'desk_no' => 'UB'.fake()->numberBetween(2, 9).'0'.fake()->numberBetween(1, 9),
        ];
    }
}
