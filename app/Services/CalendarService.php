<?php

namespace App\Services;

use App\Enums\PostType;
use App\Models\ConsultationPost;
use App\Models\ExamSchedule;
use App\Models\Post;
use App\Models\User;
use App\Support\CalendarEntry;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Builds each user's dashboard calendar. Entries are derived at read time from
 * the events they marked interest in, the published exam schedule and their
 * consultation slots, so nothing can drift out of sync.
 */
class CalendarService
{
    /**
     * @return Collection<string, Collection<int, CalendarEntry>>
     */
    public function entriesForMonth(User $user, Carbon $month): Collection
    {
        $start = $month->copy()->startOfMonth()->startOfWeek(Carbon::SUNDAY);
        $end = $month->copy()->endOfMonth()->endOfWeek(Carbon::SATURDAY);

        return $this->interestedEvents($user, $start, $end)
            ->merge($this->exams($start, $end))
            ->merge($this->consultations($user, $start, $end))
            ->sortBy(fn (CalendarEntry $entry) => $entry->startsAt)
            ->groupBy(fn (CalendarEntry $entry) => $entry->dateKey());
    }

    /**
     * @return Collection<int, CalendarEntry>
     */
    public function upcoming(User $user, int $limit = 5): Collection
    {
        $today = now()->startOfDay();
        $horizon = $today->copy()->addMonths(3);

        return $this->interestedEvents($user, $today, $horizon)
            ->merge($this->exams($today, $horizon))
            ->merge($this->consultations($user, $today, $horizon))
            ->sortBy(fn (CalendarEntry $entry) => $entry->startsAt)
            ->take($limit)
            ->values();
    }

    /**
     * @return array<int, array<int, array{date: Carbon, inMonth: bool, isToday: bool}>>
     */
    public function grid(Carbon $month): array
    {
        $cursor = $month->copy()->startOfMonth()->startOfWeek(Carbon::SUNDAY);
        $end = $month->copy()->endOfMonth()->endOfWeek(Carbon::SATURDAY);

        $weeks = [];

        while ($cursor <= $end) {
            $week = [];

            for ($day = 0; $day < 7; $day++) {
                $week[] = [
                    'date' => $cursor->copy(),
                    'inMonth' => $cursor->month === $month->month,
                    'isToday' => $cursor->isToday(),
                ];

                $cursor->addDay();
            }

            $weeks[] = $week;
        }

        return $weeks;
    }

    /**
     * @return Collection<int, CalendarEntry>
     */
    private function interestedEvents(User $user, Carbon $start, Carbon $end): Collection
    {
        return Post::query()
            ->ofType(PostType::Event)
            ->whereHas('eventInterests', fn ($query) => $query->whereBelongsTo($user))
            ->whereHas('event', fn ($query) => $query->whereBetween('event_date', [$start->toDateString(), $end->toDateString()]))
            ->with('event')
            ->get()
            ->map(fn (Post $post) => new CalendarEntry(
                title: $post->event->event_name,
                startsAt: $post->event->startsAt(),
                kind: 'event',
                detail: $post->event->venue,
                url: route('events.index'),
            ))
            ->toBase();
    }

    /**
     * @return Collection<int, CalendarEntry>
     */
    private function exams(Carbon $start, Carbon $end): Collection
    {
        return ExamSchedule::query()
            ->whereBetween('exam_date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->map(fn (ExamSchedule $exam) => new CalendarEntry(
                title: $exam->course_code.' exam',
                startsAt: $exam->startsAt(),
                kind: 'exam',
                detail: 'Section '.$exam->section.' · Room '.$exam->room_number,
                url: route('exams.index'),
            ))
            ->toBase();
    }

    /**
     * @return Collection<int, CalendarEntry>
     */
    private function consultations(User $user, Carbon $start, Carbon $end): Collection
    {
        $query = ConsultationPost::query()
            ->whereBetween('consultation_date', [$start->toDateString(), $end->toDateString()])
            ->with('post.author');

        if ($user->isFaculty()) {
            $query->whereHas('post', fn ($inner) => $inner->whereBelongsTo($user, 'author'));
        } else {
            $query->whereHas('bookings', fn ($inner) => $inner->whereBelongsTo($user));
        }

        return $query->get()->map(fn (ConsultationPost $consultation) => new CalendarEntry(
            title: $consultation->course_code.' consultation',
            startsAt: $consultation->startsAt(),
            kind: 'consultation',
            detail: $user->isFaculty()
                ? 'Room '.($consultation->room ?? 'TBA')
                : 'with '.$consultation->post->author->name,
            url: route('consultations.index'),
        ))->toBase();
    }
}
