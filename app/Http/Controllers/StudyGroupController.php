<?php

namespace App\Http\Controllers;

use App\Actions\CreatePost;
use App\Enums\PostType;
use App\Http\Controllers\Concerns\ManagesPostSection;
use App\Models\Post;
use App\Services\StudyGroupChat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StudyGroupController extends Controller
{
    use ManagesPostSection;

    public function __construct(
        private CreatePost $createPost,
        private StudyGroupChat $chat,
    ) {}

    protected function type(): PostType
    {
        return PostType::StudyGroup;
    }

    public function index(Request $request): View
    {
        $this->chat->purgeExpired();

        $posts = $this->feed()
            ->with([
                'studyGroup.members.user',
                'studyGroup.messages' => fn ($query) => $query->with('user')->oldest(),
            ])
            ->paginate(10);

        return view('study-groups.index', ['posts' => $posts]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAuthoring();

        $validated = $request->validate([
            'course_name' => ['required', 'string', 'max:15'],
            'max_members' => ['required', 'integer', 'min:2', 'max:50'],
            'content' => ['required', 'string', 'max:5000'],
        ]);

        $this->createPost->handle($request->user(), $this->type(), $validated['content'], [
            'course_name' => Str::upper($validated['course_name']),
            'max_members' => $validated['max_members'],
        ]);

        return back()->with('status', 'Study group created.');
    }

    /**
     * Joining opens the group chat. The first member starts the countdown.
     */
    public function join(Request $request, Post $post): RedirectResponse
    {
        $this->guardType($post);

        $group = $post->studyGroup;

        if ($group->members()->whereBelongsTo($request->user())->exists()) {
            return back();
        }

        if ($group->isFull()) {
            return back()->with('error', 'This study group is already full.');
        }

        $this->chat->join($group, $request->user());

        return back()->with('status', 'You joined the group — the chat box is open.');
    }

    public function messages(Request $request, Post $post): JsonResponse
    {
        $this->guardType($post);

        $group = $post->studyGroup;

        abort_unless($this->chat->isMember($group, $request->user()), 403);

        if ($group->chatHasExpired()) {
            $this->chat->purge($group);

            return response()->json(['messages' => [], 'expired' => true], 410);
        }

        return response()->json([
            'messages' => $this->chat->messagesAfter($group, $request->integer('after')),
            'expired' => false,
        ]);
    }

    public function sendMessage(Request $request, Post $post): JsonResponse
    {
        $this->guardType($post);

        $group = $post->studyGroup;

        abort_unless($this->chat->isMember($group, $request->user()), 403);

        if ($group->chatHasExpired()) {
            $this->chat->purge($group);

            return response()->json(['expired' => true], 410);
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $this->chat->send($group, $request->user(), $validated['body']);

        return response()->json(['sent' => true], 201);
    }
}
