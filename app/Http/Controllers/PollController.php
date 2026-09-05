<?php

namespace App\Http\Controllers;

use App\Models\Poll;
use App\Services\FileUploader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PollController extends Controller
{
    public function __construct(private FileUploader $uploader) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Poll::class);

        $polls = Poll::query()
            ->with(['author', 'votes'])
            ->withCount([
                'votes as yes_count' => fn ($query) => $query->where('choice', true),
                'votes as no_count' => fn ($query) => $query->where('choice', false),
            ])
            ->latest()
            ->paginate(10);

        return view('polls.index', ['polls' => $polls]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Poll::class);

        $validated = $request->validate([
            'question' => ['required', 'string', 'max:500'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.config('stuhive.uploads.max_image_kb')],
            'closes_at' => ['nullable', 'date', 'after:now'],
        ]);

        $request->user()->polls()->create([
            'question' => $validated['question'],
            'closes_at' => $validated['closes_at'] ?? null,
            'image_path' => $request->hasFile('image')
                ? $this->uploader->store($request->file('image'), 'polls')
                : null,
        ]);

        $request->user()->awardPoints(config('stuhive.points.post'));

        return back()->with('status', 'Poll published.');
    }

    public function vote(Request $request, Poll $poll): RedirectResponse
    {
        Gate::authorize('vote', $poll);

        $validated = $request->validate([
            'choice' => ['required', 'boolean'],
        ]);

        $poll->votes()->updateOrCreate(
            ['user_id' => $request->user()->id],
            ['choice' => $validated['choice']],
        );

        return back()->with('status', 'Vote recorded.');
    }

    public function destroy(Poll $poll): RedirectResponse
    {
        Gate::authorize('delete', $poll);

        $poll->delete();

        return back()->with('status', 'Poll removed.');
    }
}
