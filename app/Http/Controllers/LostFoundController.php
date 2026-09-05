<?php

namespace App\Http\Controllers;

use App\Actions\CreatePost;
use App\Enums\PostType;
use App\Http\Controllers\Concerns\ManagesPostSection;
use App\Http\Requests\StoreLostFoundRequest;
use App\Models\Post;
use App\Services\FileUploader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LostFoundController extends Controller
{
    use ManagesPostSection;

    public function __construct(
        private CreatePost $createPost,
        private FileUploader $uploader,
    ) {}

    protected function type(): PostType
    {
        return PostType::LostFound;
    }

    public function index(Request $request): View
    {
        $showResolved = $request->boolean('resolved');

        $posts = $this->feed()
            ->when(
                $showResolved,
                fn ($query) => $query
                    ->whereHas('lostFound', fn ($inner) => $inner->where('is_found', true))
                    ->unless($request->user()->isAdmin(), fn ($inner) => $inner->whereBelongsTo($request->user(), 'author')),
                fn ($query) => $query->whereHas('lostFound', fn ($inner) => $inner->where('is_found', false)),
            )
            ->paginate(10)
            ->withQueryString();

        return view('lost-found.index', [
            'posts' => $posts,
            'showResolved' => $showResolved,
            'resolvedCount' => Post::query()
                ->ofType($this->type())
                ->whereHas('lostFound', fn ($inner) => $inner->where('is_found', true))
                ->unless($request->user()->isAdmin(), fn ($query) => $query->whereBelongsTo($request->user(), 'author'))
                ->count(),
        ]);
    }

    public function store(StoreLostFoundRequest $request): RedirectResponse
    {
        $this->authorizeAuthoring();

        $detail = $request->safe()->only(['item_name', 'location']);

        if ($request->hasFile('image')) {
            $detail['image_path'] = $this->uploader->store($request->file('image'), 'lost-found');
        }

        $this->createPost->handle($request->user(), $this->type(), $request->validated('content'), $detail);

        return back()->with('status', 'Lost item posted.');
    }

    /**
     * Marks the item as found. The post leaves the public feed while the row
     * itself stays in the database.
     */
    public function markFound(Request $request, Post $post): RedirectResponse
    {
        $this->guardType($post);

        abort_unless($request->user()->id === $post->user_id, 403);

        $post->lostFound->update([
            'is_found' => true,
            'found_at' => now(),
        ]);

        $request->user()->awardPoints(config('stuhive.points.found_item'));

        return back()->with('status', 'Marked as found — the post is now hidden from the feed.');
    }
}
