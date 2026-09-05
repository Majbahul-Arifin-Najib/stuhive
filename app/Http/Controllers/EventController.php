<?php

namespace App\Http\Controllers;

use App\Actions\CreatePost;
use App\Enums\PostType;
use App\Http\Controllers\Concerns\ManagesPostSection;
use App\Models\Post;
use App\Services\FileUploader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    use ManagesPostSection;

    public function __construct(
        private CreatePost $createPost,
        private FileUploader $uploader,
    ) {}

    protected function type(): PostType
    {
        return PostType::Event;
    }

    public function index(Request $request): View
    {
        $posts = $this->feed()
            ->withCount('eventInterests')
            ->with(['eventInterests' => fn ($query) => $query->whereBelongsTo($request->user())])
            ->paginate(10);

        return view('events.index', ['posts' => $posts]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAuthoring();

        $validated = $request->validate([
            'event_name' => ['required', 'string', 'max:200'],
            'club_name' => ['nullable', 'string', 'max:120'],
            'venue' => ['nullable', 'string', 'max:120'],
            'event_date' => ['required', 'date', 'after_or_equal:today'],
            'event_time' => ['required', 'date_format:H:i'],
            'content' => ['required', 'string', 'max:5000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.config('stuhive.uploads.max_image_kb')],
        ]);

        $detail = collect($validated)->only(['event_name', 'club_name', 'venue', 'event_date', 'event_time'])->all();

        if ($request->hasFile('image')) {
            $detail['image_path'] = $this->uploader->store($request->file('image'), 'events');
        }

        $this->createPost->handle($request->user(), $this->type(), $validated['content'], $detail);

        return back()->with('status', 'Event announced.');
    }

    /**
     * Adds or removes the event from the student's dashboard calendar.
     */
    public function toggleInterest(Request $request, Post $post): RedirectResponse
    {
        $this->guardType($post);

        $existing = $post->eventInterests()->whereBelongsTo($request->user())->first();

        if ($existing) {
            $existing->delete();

            return back()->with('status', 'Removed from your calendar.');
        }

        $post->eventInterests()->create(['user_id' => $request->user()->id]);

        return back()->with('status', 'Added to your calendar.');
    }
}
