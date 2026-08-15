<?php

namespace App\Http\Controllers;

use App\Actions\CreatePost;
use App\Enums\PostType;
use App\Http\Controllers\Concerns\ManagesPostSection;
use App\Models\Post;
use App\Services\FileUploader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResourceController extends Controller
{
    use ManagesPostSection;

    public function __construct(
        private CreatePost $createPost,
        private FileUploader $uploader,
    ) {}

    protected function type(): PostType
    {
        return PostType::Note;
    }

    public function index(Request $request): View
    {
        $search = $request->string('q')->trim()->value();

        $posts = $this->feed()
            ->when($search !== '', fn ($query) => $query->whereHas(
                'note',
                fn ($inner) => $inner
                    ->where('course_code', 'like', "%{$search}%")
                    ->orWhere('faculty_initial', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
            ))
            ->paginate(10)
            ->withQueryString();

        return view('resources.index', [
            'posts' => $posts,
            'search' => $search,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAuthoring();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'course_code' => ['required', 'string', 'max:15'],
            'faculty_initial' => ['required', 'string', 'max:10'],
            'content' => ['required', 'string', 'max:5000'],
            'material' => ['required', 'file', 'mimes:pdf', 'max:'.config('stuhive.uploads.max_pdf_kb')],
        ]);

        $file = $request->file('material');

        $this->createPost->handle($request->user(), $this->type(), $validated['content'], [
            'title' => $validated['title'],
            'course_code' => Str::upper($validated['course_code']),
            'faculty_initial' => Str::upper($validated['faculty_initial']),
            'file_path' => $this->uploader->store($file, 'resources'),
            'original_filename' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
        ]);

        return back()->with('status', 'Notes shared with the hive.');
    }

    public function download(Post $post): StreamedResponse
    {
        $this->guardType($post);

        Gate::authorize('view', $post);

        $note = $post->note;
        $disk = Storage::disk(config('stuhive.uploads.disk'));

        abort_unless($disk->exists($note->file_path), 404);

        $note->increment('download_count');

        return $disk->download($note->file_path, $note->original_filename);
    }
}
