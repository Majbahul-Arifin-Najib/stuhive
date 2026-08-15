<?php

/**
 * ============================================================
 *  Feature: Campus Pet Allocation — Unit Test Suite
 *  Student ID  : 22301565
 *  Course      : CSE 470 — Software Quality Assurance
 *  Framework   : Pest PHP (Laravel)
 *  File Name   : ID22301565_campus_pets.test.php
 *                (Note: PHP class names cannot begin with a digit,
 *                 so the student ID is prefixed with "ID")
 * ============================================================
 *
 *  Routes under test
 *  -----------------
 *  GET    /pets              -> pets.index    (browse pet feed)
 *  POST   /pets              -> pets.store    (create pet post)
 *  DELETE /pets/{post}       -> pets.destroy  (delete pet post)
 *
 *  Test Cases
 *  ----------
 *  Case A - Positive / Happy Path
 *    Test 1 : Authenticated student can view the pets feed
 *    Test 2 : Authenticated student can create a pet post (text only)
 *    Test 3 : Authenticated student can create a pet post with an image
 *    Test 4 : Pet post author can delete their own post
 *
 *  Case B - Negative / Error Handling
 *    Test 5 : Creating a pet post without required 'content' returns validation error
 *    Test 6 : Deleting a post that does not exist returns 404
 *
 *  Case C - Security and Boundary
 *    Test 7 : Guest (unauthenticated) cannot view the pets feed
 *    Test 8 : Guest cannot create a pet post
 *    Test 9 : Faculty user cannot create a pet post (403 Forbidden)
 *    Test 10: A student cannot delete another student's pet post (403 Forbidden)
 */

use App\Enums\PostType;
use App\Models\PetPost;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

// ============================================================
//  All tests are wrapped in a describe block labelled with the
//  student ID and feature name, as required by the assignment.
// ============================================================

describe('Feature: Campus Pet Allocation (Student ID: 22301565)', function () {

    // ============================================================
    //  Case A - Positive / Happy Path Tests
    // ============================================================

    test('[Case A | Test 1] authenticated student can browse the pets feed', function () {
        // ARRANGE: Create a student user and a pet post they can see
        $student = User::factory()->student()->create();

        $post = Post::factory()->ofType(PostType::Pet)->for($student, 'author')->create([
            'content' => 'A fluffy orange cat was sitting outside UB1.',
        ]);
        PetPost::factory()->create([
            'post_id'    => $post->id,
            'pet_name'   => 'Kitkat',
            'spotted_at' => 'UB1 entrance',
        ]);

        // ACT & ASSERT: Page loads successfully (HTTP 200) and shows the pet name
        $this->actingAs($student)
            ->get(route('pets.index'))
            ->assertOk()
            ->assertSee('Kitkat');
    });

    // ----------------------------------------------------------------

    test('[Case A | Test 2] student can create a pet post without an image', function () {
        // ARRANGE
        $student = User::factory()->student()->create();

        // ACT: POST the form with required fields (no image)
        $this->actingAs($student)
            ->post(route('pets.store'), [
                'pet_name'   => 'Momo',
                'spotted_at' => 'Cafeteria back gate',
                'content'    => 'Spotted a small brown dog near the cafeteria today.',
            ])
            ->assertRedirect(); // expects a redirect back on success

        // ASSERT: The post was saved in the database with correct data
        $post = Post::ofType(PostType::Pet)
            ->where('user_id', $student->id)
            ->first();

        expect($post)->not->toBeNull()
            ->and($post->content)->toBe('Spotted a small brown dog near the cafeteria today.')
            ->and($post->pet->pet_name)->toBe('Momo')
            ->and($post->pet->spotted_at)->toBe('Cafeteria back gate');
    });

    // ----------------------------------------------------------------

    test('[Case A | Test 3] student can create a pet post with an uploaded image', function () {
        // ARRANGE: Use fake storage so no real files are written to disk
        Storage::fake('public');

        $student = User::factory()->student()->create();

        // ACT
        $this->actingAs($student)
            ->post(route('pets.store'), [
                'pet_name'   => 'Tiger',
                'spotted_at' => 'Library lawn',
                'content'    => 'A striped tabby cat is sleeping on the library lawn.',
                'image'      => UploadedFile::fake()->image('tiger.jpg'),
            ])
            ->assertRedirect();

        // ASSERT: Image path was stored in the database
        $post = Post::ofType(PostType::Pet)->where('user_id', $student->id)->first();

        expect($post)->not->toBeNull()
            ->and($post->pet->image_path)->not->toBeNull();

        // Verify the uploaded file actually exists on the fake storage disk
        Storage::disk('public')->assertExists($post->pet->image_path);
    });

    // ----------------------------------------------------------------

    test('[Case A | Test 4] the author of a pet post can delete their own post', function () {
        // ARRANGE: Create a student and a pet post owned by that student
        $student = User::factory()->student()->create();

        $post = Post::factory()->ofType(PostType::Pet)->for($student, 'author')->create([
            'content' => 'A tiny kitten near the parking lot.',
        ]);
        PetPost::factory()->create(['post_id' => $post->id]);

        // ACT
        $this->actingAs($student)
            ->delete(route('pets.destroy', $post))
            ->assertRedirect();

        // ASSERT: The post was soft-deleted (row still exists, deleted_at is set)
        $this->assertSoftDeleted('posts', ['id' => $post->id]);
    });

    // ============================================================
    //  Case B - Negative / Error Handling Tests
    // ============================================================

    test('[Case B | Test 5] creating a pet post without the required content field returns a validation error', function () {
        // ARRANGE
        $student = User::factory()->student()->create();

        // ACT: Send the form WITHOUT the required 'content' field
        $this->actingAs($student)
            ->post(route('pets.store'), [
                'pet_name'   => 'Coco',
                'spotted_at' => 'Parking lot',
                // 'content' is intentionally omitted to trigger validation
            ])
            ->assertSessionHasErrors('content'); // expects validation error on 'content'

        // ASSERT: No pet post was created in the database
        $this->assertDatabaseCount('posts', 0);
    });

    // ----------------------------------------------------------------

    test('[Case B | Test 6] deleting a pet post that does not exist returns 404', function () {
        // ARRANGE
        $student = User::factory()->student()->create();

        // ACT: Use an ID that certainly does not exist (e.g. 999999)
        $this->actingAs($student)
            ->delete(route('pets.destroy', 999999))
            ->assertNotFound(); // expects HTTP 404 Not Found
    });

    // ============================================================
    //  Case C - Security and Boundary Tests
    // ============================================================

    test('[Case C | Test 7] guest (unauthenticated) cannot view the pets feed and is redirected to login', function () {
        // ACT: Hit the pets index WITHOUT any authentication
        $this->get(route('pets.index'))
            ->assertRedirect(route('login')); // must redirect to login page
    });

    // ----------------------------------------------------------------

    test('[Case C | Test 8] guest cannot create a pet post and is redirected to login', function () {
        // ACT: POST to pets.store without being logged in
        $this->post(route('pets.store'), [
            'content' => 'A cat near the library.',
        ])
            ->assertRedirect(route('login'));

        // ASSERT: Nothing was saved to the database
        $this->assertDatabaseCount('posts', 0);
    });

    // ----------------------------------------------------------------

    test('[Case C | Test 9] faculty user cannot create a pet post and receives 403 Forbidden', function () {
        // ARRANGE: Pets can only be created by students
        // (PostType::Pet -> authorRoles() returns [Role::Student] only)
        $faculty = User::factory()->faculty()->create();

        // ACT
        $this->actingAs($faculty)
            ->post(route('pets.store'), [
                'pet_name'   => 'Bhutu',
                'spotted_at' => 'Main gate',
                'content'    => 'A friendly dog at the main gate.',
            ])
            ->assertForbidden(); // expects HTTP 403 Forbidden

        // ASSERT: No pet post was created
        $this->assertDatabaseCount('posts', 0);
    });

    // ----------------------------------------------------------------

    test('[Case C | Test 10] a student cannot delete another student\'s pet post and receives 403 Forbidden', function () {
        // ARRANGE: Two different student accounts
        $owner = User::factory()->student()->create();
        $other = User::factory()->student()->create();

        $post = Post::factory()->ofType(PostType::Pet)->for($owner, 'author')->create([
            'content' => 'A cat belonging to the owner student.',
        ]);
        PetPost::factory()->create(['post_id' => $post->id]);

        // ACT: The *other* student tries to delete the *owner*'s post
        $this->actingAs($other)
            ->delete(route('pets.destroy', $post))
            ->assertForbidden(); // expects HTTP 403 Forbidden

        // ASSERT: Post was NOT deleted (deleted_at is still null)
        $this->assertDatabaseHas('posts', ['id' => $post->id, 'deleted_at' => null]);
    });
});
