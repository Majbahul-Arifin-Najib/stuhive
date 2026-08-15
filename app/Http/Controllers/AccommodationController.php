<?php

namespace App\Http\Controllers;

use App\Actions\CreatePost;
use App\Enums\PostType;
use App\Http\Controllers\Concerns\ManagesPostSection;
use App\Services\FileUploader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccommodationController extends Controller
{
    use ManagesPostSection;

    public function __construct(
        private CreatePost $createPost,
        private FileUploader $uploader,
    ) {}

    protected function type(): PostType
    {
        return PostType::Accommodation;
    }

    public function index(Request $request): View
    {
        $search = $request->string('q')->trim()->value();

        $posts = $this->feed()
            ->when($search !== '', fn ($query) => $query->whereHas(
                'accommodation',
                fn ($inner) => $inner->where('area', 'like', "%{$search}%")
            ))
            ->paginate(10)
            ->withQueryString();

        return view('accommodations.index', [
            'posts' => $posts,
            'search' => $search,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAuthoring();

        $validated = $request->validate([
            'area' => ['required', 'string', 'max:100'],
            'walking_distance' => ['required', 'string', 'max:20'],
            'phone_number' => ['required', 'string', 'max:20'],
            'rent' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'content' => ['required', 'string', 'max:5000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.config('stuhive.uploads.max_image_kb')],
        ]);

        $detail = collect($validated)->only(['area', 'walking_distance', 'phone_number', 'rent'])->all();

        if ($request->hasFile('image')) {
            $detail['image_path'] = $this->uploader->store($request->file('image'), 'accommodations');
        }

        $this->createPost->handle($request->user(), $this->type(), $validated['content'], $detail);

        return back()->with('status', 'Accommodation listed.');
    }
}
