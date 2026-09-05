<?php

namespace Database\Factories;

use App\Enums\PostType;
use App\Models\CourseReviewPost;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CourseReviewPost>
 */
class CourseReviewPostFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'post_id' => Post::factory()->ofType(PostType::CourseReview),
            'course_code' => fake()->randomElement(['CSE220', 'CSE320', 'CSE470', 'MAT215', 'ECO101']),
            'faculty_initial' => Str::upper(fake()->lexify('???')),
            'rating' => fake()->numberBetween(1, 5),
        ];
    }
}
