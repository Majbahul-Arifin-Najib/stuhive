<?php

use App\Enums\PostType;
use App\Models\EventPost;
use App\Models\ExamSchedule;
use App\Models\Post;
use App\Models\User;

test('showing interest puts the event on the student calendar', function () {
    $student = User::factory()->student()->create();
    $post = Post::factory()->ofType(PostType::Event)->create();

    EventPost::factory()->create([
        'post_id' => $post->id,
        'event_name' => 'Intra Hackathon',
        'event_date' => now()->addDays(5)->toDateString(),
        'event_time' => '14:00:00',
    ]);

    $this->actingAs($student)->get(route('dashboard'))->assertDontSee('Intra Hackathon');

    $this->actingAs($student)->post(route('events.interest', $post))->assertRedirect();

    $this->assertDatabaseHas('event_interests', [
        'post_id' => $post->id,
        'user_id' => $student->id,
    ]);

    $this->actingAs($student)->get(route('dashboard'))->assertSee('Intra Hackathon');
});

test('tapping interested again removes it from the calendar', function () {
    $student = User::factory()->student()->create();
    $post = Post::factory()->ofType(PostType::Event)->create();
    EventPost::factory()->create(['post_id' => $post->id, 'event_date' => now()->addDay()->toDateString()]);

    $this->actingAs($student)->post(route('events.interest', $post));
    $this->actingAs($student)->post(route('events.interest', $post));

    $this->assertDatabaseMissing('event_interests', [
        'post_id' => $post->id,
        'user_id' => $student->id,
    ]);
});

test('published exams appear on every student calendar', function () {
    $student = User::factory()->student()->create();

    ExamSchedule::factory()->create([
        'course_code' => 'CSE370',
        'exam_date' => now()->addDays(3)->toDateString(),
        'exam_time' => '09:00:00',
    ]);

    $this->actingAs($student)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('CSE370');
});

test('the exam schedule can be searched', function () {
    $student = User::factory()->student()->create();

    ExamSchedule::factory()->create(['course_code' => 'CSE220', 'exam_date' => now()->addDay()->toDateString()]);
    ExamSchedule::factory()->create(['course_code' => 'MAT216', 'exam_date' => now()->addDay()->toDateString()]);

    $this->actingAs($student)
        ->get(route('exams.index', ['q' => 'CSE220']))
        ->assertSee('CSE220')
        ->assertDontSee('MAT216');
});

test('students cannot add exams but faculty and admins can', function () {
    $payload = [
        'course_code' => 'CSE470',
        'section' => '07',
        'exam_date' => now()->addWeek()->toDateString(),
        'exam_time' => '11:30',
        'room_number' => 'UB4-08',
    ];

    $this->actingAs(User::factory()->student()->create())
        ->post(route('exams.store'), $payload)
        ->assertForbidden();

    $this->actingAs(User::factory()->admin()->create())
        ->post(route('exams.store'), $payload)
        ->assertRedirect();

    $this->assertDatabaseHas('exam_schedules', ['course_code' => 'CSE470', 'room_number' => 'UB4-08']);
});
