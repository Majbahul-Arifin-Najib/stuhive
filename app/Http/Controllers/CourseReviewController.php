<?php

namespace App\Http\Controllers;

use App\Actions\CreatePost;
use App\Enums\PostType;
use App\Http\Controllers\Concerns\ManagesPostSection;
use App\Services\FileUploader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CourseReviewController extends Controller
{
    use ManagesPostSection;

    public function __construct(
        private CreatePost $createPost,
        private FileUploader $uploader,
    ) {}

    protected function type(): PostType
    {
        return PostType::CourseReview;
    }

    public function index(Request $request): View
    {
        $search = $request->string('q')->trim()->value();

        $posts = $this->feed()
            ->when($search !== '', fn ($query) => $query->whereHas(
                'courseReview',
                fn ($inner) => $inner
                    ->where('course_code', 'like', "%{$search}%")
                    ->orWhere('faculty_initial', 'like', "%{$search}%")
            ))
            ->paginate(10)
            ->withQueryString();

        return view('course-reviews.index', [
            'posts' => $posts,
            'search' => $search,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAuthoring();

        $validated = $request->validate([
            'course_code' => ['required', 'string', 'max:15'],
            'faculty_initial' => ['nullable', 'string', 'max:10'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'content' => ['required', 'string', 'max:5000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.config('stuhive.uploads.max_image_kb')],
        ]);

        $detail = [
            'course_code' => Str::upper($validated['course_code']),
            'faculty_initial' => filled($validated['faculty_initial'] ?? null)
                ? Str::upper($validated['faculty_initial'])
                : null,
            'rating' => $validated['rating'] ?? null,
        ];

        if ($request->hasFile('image')) {
            $detail['image_path'] = $this->uploader->store($request->file('image'), 'course-reviews');
        }

        $this->createPost->handle($request->user(), $this->type(), $validated['content'], $detail);

        return back()->with('status', 'Review shared.');
    }
}
