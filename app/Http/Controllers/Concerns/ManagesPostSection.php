<?php

namespace App\Http\Controllers\Concerns;

use App\Enums\PostType;
use App\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

/**
 * Shared feed, guard and delete behaviour for the sections that are backed by
 * a post row plus a type specific detail row.
 */
trait ManagesPostSection
{
    abstract protected function type(): PostType;

    protected function feed(): Builder
    {
        $type = $this->type();

        $query = Post::query()
            ->ofType($type)
            ->with(['author', $type->detailRelation()])
            ->latest();

        if ($type->allowsReactions()) {
            $query->with('reactions');
        }

        if ($type->allowsComments()) {
            $query->with(['comments' => fn ($inner) => $inner->with('author')->oldest()]);
        }

        return $query;
    }

    protected function paginateFeed(int $perPage = 10): LengthAwarePaginator
    {
        return $this->feed()->paginate($perPage)->withQueryString();
    }

    /**
     * Guarantees a `{post}` route parameter really belongs to this section.
     */
    protected function guardType(Post $post): void
    {
        abort_unless($post->type === $this->type(), 404);
    }

    protected function authorizeAuthoring(): void
    {
        abort_unless($this->type()->creatableBy(request()->user()?->role), 403);
    }

    public function destroy(Post $post): RedirectResponse
    {
        $this->guardType($post);

        Gate::authorize('delete', $post);

        $post->delete();

        return back()->with('status', 'Post removed.');
    }
}
