<?php

namespace App\Http\Controllers;

use App\Models\ExamSchedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ExamScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('q')->trim()->value();

        $exams = ExamSchedule::query()
            ->search($search)
            ->when(! $request->boolean('past'), fn ($query) => $query->upcoming())
            ->orderBy('exam_date')
            ->orderBy('exam_time')
            ->paginate(20)
            ->withQueryString();

        return view('exams.index', [
            'exams' => $exams,
            'search' => $search,
            'showPast' => $request->boolean('past'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'course_code' => ['required', 'string', 'max:15'],
            'section' => ['required', 'string', 'max:5'],
            'exam_date' => ['required', 'date'],
            'exam_time' => ['required', 'date_format:H:i'],
            'room_number' => ['required', 'string', 'max:10'],
        ]);

        ExamSchedule::create([
            ...$validated,
            'course_code' => Str::upper($validated['course_code']),
            'room_number' => Str::upper($validated['room_number']),
            'day' => Carbon::parse($validated['exam_date'])->format('l'),
        ]);

        return back()->with('status', 'Exam added to the schedule.');
    }

    public function destroy(ExamSchedule $exam): RedirectResponse
    {
        $exam->delete();

        return back()->with('status', 'Exam removed from the schedule.');
    }
}
