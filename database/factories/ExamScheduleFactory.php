<?php

namespace Database\Factories;

use App\Models\ExamSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<ExamSchedule>
 */
class ExamScheduleFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date = Carbon::parse(fake()->dateTimeBetween('now', '+6 weeks')->format('Y-m-d'));

        return [
            'course_code' => fake()->randomElement(['CSE220', 'CSE370', 'CSE470', 'MAT216', 'PHY111', 'ENG102']),
            'section' => (string) fake()->numberBetween(1, 20),
            'day' => $date->format('l'),
            'exam_date' => $date->toDateString(),
            'exam_time' => fake()->randomElement(['09:00:00', '11:30:00', '14:00:00', '16:30:00']),
            'room_number' => 'UB'.fake()->numberBetween(20, 99).'0'.fake()->numberBetween(1, 9),
        ];
    }
}
