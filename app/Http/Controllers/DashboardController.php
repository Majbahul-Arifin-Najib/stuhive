<?php

namespace App\Http\Controllers;

use App\Enums\PostType;
use App\Enums\Role;
use App\Models\ConsultationBooking;
use App\Models\ExamSchedule;
use App\Models\Poll;
use App\Models\Post;
use App\Models\User;
use App\Services\BudgetService;
use App\Services\CalendarService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private CalendarService $calendar,
        private BudgetService $budgets,
    ) {}

    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $month = $this->resolveMonth($request->query('month'));

        $shared = [
            'user' => $user,
            'month' => $month,
            'weeks' => $this->calendar->grid($month),
            'entries' => $this->calendar->entriesForMonth($user, $month),
            'upcoming' => $this->calendar->upcoming($user),
            'notes' => $user->notes()->latest('updated_at')->take(4)->get(),
        ];

        return match ($user->role) {
            Role::Faculty => view('dashboard.faculty', [...$shared, ...$this->facultyData($user)]),
            Role::Admin => view('dashboard.admin', [...$shared, ...$this->adminData()]),
            Role::Student => view('dashboard.student', [...$shared, ...$this->studentData($user)]),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function studentData(User $user): array
    {
        return [
            'postCount' => $user->posts()->count(),
            'interestCount' => $user->eventInterests()->count(),
            'bookingCount' => $user->consultationBookings()->count(),
            'budget' => $this->budgets->overview($user, now()),
            'nextExams' => ExamSchedule::upcoming()->orderBy('exam_date')->orderBy('exam_time')->take(4)->get(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function facultyData(User $user): array
    {
        $consultationIds = $user->posts()->ofType(PostType::Consultation)->pluck('id');

        return [
            'consultationCount' => $consultationIds->count(),
            'bookingCount' => ConsultationBooking::whereIn('post_id', $consultationIds)->count(),
            'recentBookings' => ConsultationBooking::whereIn('post_id', $consultationIds)
                ->with(['user', 'consultation'])
                ->latest()
                ->take(6)
                ->get(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function adminData(): array
    {
        return [
            'userCounts' => User::query()
                ->selectRaw('role, count(*) as total')
                ->groupBy('role')
                ->pluck('total', 'role'),
            'postCount' => Post::count(),
            'postsToday' => Post::whereDate('created_at', today())->count(),
            'pollCount' => Poll::count(),
            'latestPosts' => Post::with('author')->latest()->take(6)->get(),
        ];
    }

    private function resolveMonth(?string $value): Carbon
    {
        try {
            return $value ? Carbon::createFromFormat('Y-m', $value)->startOfMonth() : now()->startOfMonth();
        } catch (\Throwable) {
            return now()->startOfMonth();
        }
    }
}
