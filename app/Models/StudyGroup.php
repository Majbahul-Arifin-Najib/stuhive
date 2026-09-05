<?php

namespace App\Models;

use App\Models\Concerns\BelongsToPost;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['post_id', 'course_name', 'max_members', 'chat_expires_at'])]
class StudyGroup extends Model
{
    use BelongsToPost, HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'max_members' => 'integer',
            'chat_expires_at' => 'datetime',
        ];
    }

    public function members(): HasMany
    {
        return $this->hasMany(StudyGroupMember::class, 'post_id', 'post_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(StudyGroupMessage::class, 'post_id', 'post_id');
    }

    public function isFull(): bool
    {
        return $this->members()->count() >= $this->max_members;
    }

    public function chatHasExpired(): bool
    {
        return $this->chat_expires_at !== null && $this->chat_expires_at->isPast();
    }

    public function chatIsOpen(): bool
    {
        return $this->chat_expires_at !== null && $this->chat_expires_at->isFuture();
    }
}
