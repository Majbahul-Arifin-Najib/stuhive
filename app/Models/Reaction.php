<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['post_id', 'user_id', 'emoji'])]
class Reaction extends Model
{
    use HasFactory;

    /**
     * The emoji palette offered on every reactable post.
     *
     * @var array<int, string>
     */
    public const PALETTE = ['👍', '❤️', '😂', '😮', '😢', '🔥'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
