<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PostType;
use App\Http\Controllers\Controller;
use App\Models\Poll;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ModerationController extends Controller
{
    public function index(Request $request): View
    {
        $type = $request->filled('type') ? PostType::tryFrom($request->string('type')->value()) : null;
        $search = $request->string('q')->trim()->value();

        $posts = Post::query()
            ->with('author')
            ->withCount(['comments', 'reactions'])
            ->when($type, fn ($query) => $query->ofType($type))
            ->when($search !== '', fn ($query) => $query
                ->where('content', 'like', "%{$search}%")
                ->orWhereHas('author', fn ($inner) => $inner->where('name', 'like', "%{$search}%")))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.moderation', [
            'posts' => $posts,
            'polls' => Poll::with('author')->withCount('votes')->latest()->take(10)->get(),
            'type' => $type,
            'search' => $search,
        ]);
    }

    public function destroyPost(Post $post): RedirectResponse
    {
        Gate::authorize('delete', $post);

        $post->delete();

        return back()->with('status', 'Post removed. The record is kept in the database.');
    }

    public function destroyPoll(Poll $poll): RedirectResponse
    {
        Gate::authorize('delete', $poll);

        $poll->delete();

        return back()->with('status', 'Poll removed. The record is kept in the database.');
    }
}
