<?php

namespace App\Actions;

use App\Enums\PostType;
use App\Enums\Role;
use App\Models\Post;
use App\Models\User;
use App\Notifications\NewPostPublished;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class CreatePost
{
    /**
     * Creates the shared post row plus its type specific detail row, rewards
     * the author and lets the student body know about it.
     *
     * @param  array<string, mixed>  $detail
     */
    public function handle(User $author, PostType $type, string $content, array $detail = []): Post
    {
        $post = DB::transaction(function () use ($author, $type, $content, $detail): Post {
            $post = $author->posts()->create([
                'type' => $type,
                'content' => $content,
            ]);

            $post->{$type->detailRelation()}()->create($detail);

            $author->awardPoints(config('stuhive.points.post'));

            return $post;
        });

        $this->notifyStudents($post);

        return $post->load([$type->detailRelation(), 'author']);
    }

    private function notifyStudents(Post $post): void
    {
        if (! $post->type->visibleTo(Role::Student)) {
            return;
        }

        $students = User::query()
            ->role(Role::Student)
            ->whereKeyNot($post->user_id)
            ->get();

        if ($students->isNotEmpty()) {
            Notification::send($students, new NewPostPublished($post));
        }
    }
}
