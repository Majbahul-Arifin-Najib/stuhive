<?php

namespace App\Http\Controllers;

use App\Actions\CreatePost;
use App\Enums\PostType;
use App\Http\Controllers\Concerns\ManagesPostSection;
use App\Services\FileUploader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CandidController extends Controller
{
    use ManagesPostSection;

    public function __construct(
        private CreatePost $createPost,
        private FileUploader $uploader,
    ) {}

    protected function type(): PostType
    {
        return PostType::Candid;
    }

    public function index(): View
    {
        return view('candid.index', [
            'posts' => $this->paginateFeed(12),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAuthoring();

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:2000'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.config('stuhive.uploads.max_image_kb')],
        ]);

        $this->createPost->handle($request->user(), $this->type(), $validated['content'], [
            'image_path' => $this->uploader->store($request->file('image'), 'candid'),
        ]);

        return back()->with('status', 'Shared to the candid wall.');
    }
}
