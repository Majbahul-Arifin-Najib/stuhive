<?php

namespace Database\Factories;

use App\Enums\PostType;
use App\Models\EventPost;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventPost>
 */
class EventPostFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'post_id' => Post::factory()->ofType(PostType::Event),
            'event_name' => fake()->randomElement(['Intra Hackathon', 'Cultural Night', 'Robotics Expo', 'Career Fair', 'Photowalk']),
            'club_name' => fake()->randomElement(['Computer Club', 'Cultural Club', 'Robotics Club', 'Business Club']),
            'venue' => fake()->randomElement(['Auditorium', 'UB Multipurpose Hall', 'Open Field', 'Room 09A-15']),
            'event_date' => fake()->dateTimeBetween('now', '+2 months')->format('Y-m-d'),
            'event_time' => fake()->randomElement(['10:00:00', '13:30:00', '15:00:00', '18:00:00']),
        ];
    }
}
