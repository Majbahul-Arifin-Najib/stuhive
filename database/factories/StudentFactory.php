<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'student_id' => (string) fake()->unique()->numberBetween(20101000, 24301999),
            'department' => fake()->randomElement(['CSE', 'EEE', 'BBA', 'ENH', 'ARC', 'PHR']),
        ];
    }
}
