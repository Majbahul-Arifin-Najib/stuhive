<?php

namespace App\Http\Controllers;

use App\Actions\CreatePost;
use App\Enums\PostType;
use App\Http\Controllers\Concerns\ManagesPostSection;
use App\Models\Post;
use App\Models\User;
use App\Notifications\ConsultationBooked;
use App\Notifications\ConsultationPostponed;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ConsultationController extends Controller
{
    use ManagesPostSection;

    public function __construct(private CreatePost $createPost) {}

    protected function type(): PostType
    {
        return PostType::Consultation;
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        $posts = $this->feed()
            ->when($user->isFaculty(), fn ($query) => $query->whereBelongsTo($user, 'author'))
            ->with([
                'consultation.bookings' => fn ($query) => $query->with('user')->latest(),
            ])
            ->paginate(10);

        return view('consultations.index', ['posts' => $posts]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAuthoring();

        $validated = $request->validate([
            'course_code' => ['required', 'string', 'max:15'],
            'consultation_date' => ['required', 'date', 'after_or_equal:today'],
            'consultation_time' => ['required', 'date_format:H:i'],
            'room' => ['nullable', 'string', 'max:20'],
            'capacity' => ['required', 'integer', 'min:1', 'max:100'],
            'content' => ['required', 'string', 'max:5000'],
        ]);

        $this->createPost->handle($request->user(), $this->type(), $validated['content'], [
            'course_code' => Str::upper($validated['course_code']),
            'consultation_date' => $validated['consultation_date'],
            'consultation_time' => $validated['consultation_time'],
            'consultation_day' => Carbon::parse($validated['consultation_date'])->format('l'),
            'room' => $validated['room'] ?? null,
            'capacity' => $validated['capacity'],
        ]);

        return back()->with('status', 'Consultation slot published.');
    }

    public function book(Request $request, Post $post): RedirectResponse
    {
        $this->guardType($post);

        $consultation = $post->consultation;

        if ($consultation->isFull()) {
            return back()->with('error', 'This consultation slot is already full.');
        }

        $validated = $request->validate([
            'topic' => ['nullable', 'string', 'max:200'],
        ]);

        $booking = $consultation->bookings()->firstOrCreate(
            ['user_id' => $request->user()->id],
            ['topic' => $validated['topic'] ?? null],
        );

        if ($booking->wasRecentlyCreated) {
            $post->author->notify(new ConsultationBooked($consultation, $request->user()));
        }

        return back()->with('status', 'Consultation booked. Your faculty has been notified.');
    }

    public function cancelBooking(Request $request, Post $post): RedirectResponse
    {
        $this->guardType($post);

        $post->consultation->bookings()->whereBelongsTo($request->user())->delete();

        return back()->with('status', 'Booking cancelled.');
    }

    /**
     * Moves a slot to a new date and time and tells every booked student.
     */
    public function postpone(Request $request, Post $post): RedirectResponse
    {
        $this->guardType($post);

        abort_unless($request->user()->id === $post->user_id, 403);

        $validated = $request->validate([
            'consultation_date' => ['required', 'date', 'after_or_equal:today'],
            'consultation_time' => ['required', 'date_format:H:i'],
            'postpone_reason' => ['nullable', 'string', 'max:200'],
        ]);

        $consultation = $post->consultation;

        $consultation->update([
            'consultation_date' => $validated['consultation_date'],
            'consultation_time' => $validated['consultation_time'],
            'consultation_day' => Carbon::parse($validated['consultation_date'])->format('l'),
            'postpone_reason' => $validated['postpone_reason'] ?? null,
            'postponed_at' => now(),
        ]);

        $students = User::whereIn('id', $consultation->bookings()->pluck('user_id'))->get();

        if ($students->isNotEmpty()) {
            Notification::send($students, new ConsultationPostponed($consultation->fresh()));
        }

        return back()->with('status', 'Consultation rescheduled and students notified.');
    }
}
