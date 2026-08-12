<?php

namespace Database\Factories;

use App\Enums\PostType;
use App\Models\ConsultationPost;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<ConsultationPost>
 */
class ConsultationPostFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date = Carbon::parse(fake()->dateTimeBetween('now', '+3 weeks')->format('Y-m-d'));

        return [
            'post_id' => Post::factory()->ofType(PostType::Consultation)->for(User::factory()->faculty(), 'author'),
            'course_code' => fake()->randomElement(['CSE220', 'CSE370', 'CSE470']),
            'consultation_day' => $date->format('l'),
            'consultation_date' => $date->toDateString(),
            'consultation_time' => fake()->randomElement(['11:00:00', '14:00:00', '16:30:00']),
            'room' => 'UB'.fake()->numberBetween(2, 9).'0'.fake()->numberBetween(1, 9),
            'capacity' => fake()->numberBetween(3, 10),
        ];
    }
}
