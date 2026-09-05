<?php

namespace App\Services;

use App\Models\StudyGroup;
use App\Models\StudyGroupMessage;
use App\Models\User;

/**
 * The study group chat only lives for a fixed window. Once the window closes
 * the messages are deleted from the database, as required by the spec.
 */
class StudyGroupChat
{
    public function isMember(StudyGroup $group, User $user): bool
    {
        return $group->members()->whereBelongsTo($user)->exists();
    }

    /**
     * Adds a member, opening the chat window on the first join.
     */
    public function join(StudyGroup $group, User $user): void
    {
        $group->members()->create(['user_id' => $user->id]);

        if ($group->chat_expires_at === null) {
            $group->update([
                'chat_expires_at' => now()->addHours(config('stuhive.study_group_chat_hours')),
            ]);
        }
    }

    public function send(StudyGroup $group, User $user, string $body): StudyGroupMessage
    {
        return $group->messages()->create([
            'user_id' => $user->id,
            'body' => $body,
        ]);
    }

    /**
     * @return array<int, array{id: int, user_id: int, author: string, body: string, sent_at: string}>
     */
    public function messagesAfter(StudyGroup $group, int $afterId = 0): array
    {
        return $group->messages()
            ->with('user:id,name')
            ->where('id', '>', $afterId)
            ->oldest('id')
            ->limit(200)
            ->get()
            ->map(fn (StudyGroupMessage $message) => [
                'id' => $message->id,
                'user_id' => $message->user_id,
                'author' => $message->user->name,
                'body' => $message->body,
                'sent_at' => $message->created_at->format('g:i A'),
            ])
            ->all();
    }

    public function purge(StudyGroup $group): void
    {
        $group->messages()->delete();
    }

    /**
     * Drops the messages of every group whose chat window has closed.
     */
    public function purgeExpired(): int
    {
        $expired = StudyGroup::query()
            ->whereNotNull('chat_expires_at')
            ->where('chat_expires_at', '<=', now())
            ->pluck('post_id');

        if ($expired->isEmpty()) {
            return 0;
        }

        return StudyGroupMessage::whereIn('post_id', $expired)->delete();
    }
}
