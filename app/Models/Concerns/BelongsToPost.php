<?php

namespace App\Models\Concerns;

use App\Models\Post;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Shared behaviour for the per-type detail tables that extend a post row
 * one-to-one and use `post_id` as their primary key.
 */
trait BelongsToPost
{
    public function initializeBelongsToPost(): void
    {
        $this->primaryKey = 'post_id';
        $this->incrementing = false;
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
