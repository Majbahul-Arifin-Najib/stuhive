<?php

namespace App\Http\Controllers;

use App\Actions\CreatePost;
use App\Enums\PostType;
use App\Http\Controllers\Concerns\ManagesPostSection;
use App\Models\Post;
use App\Services\FileUploader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MarketplaceController extends Controller
{
    use ManagesPostSection;

    public function __construct(
        private CreatePost $createPost,
        private FileUploader $uploader,
    ) {}

    protected function type(): PostType
    {
        return PostType::Marketplace;
    }

    public function index(Request $request): View
    {
        $posts = $this->feed()
            ->unless($request->boolean('sold'), fn ($query) => $query->whereHas(
                'marketplace',
                fn ($inner) => $inner->where('is_sold', false)
            ))
            ->paginate(12)
            ->withQueryString();

        return view('marketplace.index', [
            'posts' => $posts,
            'showSold' => $request->boolean('sold'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAuthoring();

        $validated = $request->validate([
            'product_name' => ['required', 'string', 'max:120'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999'],
            'condition' => ['required', Rule::in(['new', 'like_new', 'used'])],
            'contact_number' => ['nullable', 'string', 'max:20'],
            'content' => ['required', 'string', 'max:5000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.config('stuhive.uploads.max_image_kb')],
        ]);

        $detail = collect($validated)->only(['product_name', 'price', 'condition', 'contact_number'])->all();

        if ($request->hasFile('image')) {
            $detail['image_path'] = $this->uploader->store($request->file('image'), 'marketplace');
        }

        $this->createPost->handle($request->user(), $this->type(), $validated['content'], $detail);

        return back()->with('status', 'Product listed.');
    }

    public function markSold(Request $request, Post $post): RedirectResponse
    {
        $this->guardType($post);

        abort_unless($request->user()->id === $post->user_id, 403);

        $post->marketplace->update(['is_sold' => true]);

        return back()->with('status', 'Marked as sold.');
    }
}
