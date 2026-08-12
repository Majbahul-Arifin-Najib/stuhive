<?php

namespace Database\Factories;

use App\Enums\PostType;
use App\Models\NotePost;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<NotePost>
 */
class NotePostFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $filename = Str::slug(fake()->words(3, true)).'.pdf';

        return [
            'post_id' => Post::factory()->ofType(PostType::Note),
            'title' => Str::headline(fake()->words(3, true)),
            'course_code' => fake()->randomElement(['CSE220', 'CSE370', 'MAT216', 'PHY111', 'ENG102']),
            'faculty_initial' => Str::upper(fake()->lexify('???')),
            'file_path' => 'resources/'.fake()->uuid().'.pdf',
            'original_filename' => $filename,
            'file_size' => fake()->numberBetween(50_000, 4_000_000),
        ];
    }
}
