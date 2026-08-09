<?php

namespace App\Http\Controllers;

use App\Actions\CreatePost;
use App\Enums\PostType;
use App\Http\Controllers\Concerns\ManagesPostSection;
use App\Services\FileUploader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PetController extends Controller
{
    use ManagesPostSection;

    public function __construct(
        private CreatePost $createPost,
        private FileUploader $uploader,
    ) {}

    protected function type(): PostType
    {
        return PostType::Pet;
    }

    public function index(): View
    {
        return view('pets.index', [
            'posts' => $this->paginateFeed(10),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAuthoring();

        $validated = $request->validate([
            'pet_name' => ['nullable', 'string', 'max:60'],
            'spotted_at' => ['nullable', 'string', 'max:120'],
            'content' => ['required', 'string', 'max:5000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.config('stuhive.uploads.max_image_kb')],
        ]);

        $detail = collect($validated)->only(['pet_name', 'spotted_at'])->all();

        if ($request->hasFile('image')) {
            $detail['image_path'] = $this->uploader->store($request->file('image'), 'pets');
        }

        $this->createPost->handle($request->user(), $this->type(), $validated['content'], $detail);

        return back()->with('status', 'Pet post shared.');
    }
}
