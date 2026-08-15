<?php

namespace App\Http\Controllers;

use App\Contracts\SpendingAdvisor;
use App\Models\Expense;
use App\Services\BudgetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MoneyController extends Controller
{
    public function __construct(private BudgetService $budgets) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $month = $this->resolveMonth($request->query('month'));
        $overview = $this->budgets->overview($user, $month);

        return view('money.index', [
            'month' => $month,
            'overview' => $overview,
            'expenses' => $this->budgets->expensesForMonth($user, $month),
            'summary' => $user->expenseSummaries()->whereDate('month', $month->toDateString())->first(),
            'lastClosedMonth' => $this->budgets->lastClosedMonth($user),
            'categories' => Expense::CATEGORIES,
        ]);
    }

    public function storeBudget(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'month' => ['required', 'date_format:Y-m'],
            'amount' => ['required', 'numeric', 'min:1', 'max:99999999'],
        ]);

        $month = Carbon::createFromFormat('Y-m', $validated['month'])->startOfMonth();

        $request->user()->budgets()->updateOrCreate(
            ['month' => $month->toDateString()],
            ['amount' => $validated['amount']],
        );

        return back()->with('status', 'Budget saved for '.$month->format('F Y').'.');
    }

    public function storeExpense(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'spent_on' => ['required', 'string', 'max:60'],
            'category' => ['required', Rule::in(Expense::CATEGORIES)],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:99999999'],
            'spent_at' => ['required', 'date', 'before_or_equal:today'],
        ]);

        $request->user()->expenses()->create($validated);

        $overview = $this->budgets->overview($request->user(), Carbon::parse($validated['spent_at']));

        if ($overview['isOver']) {
            return back()->with('warning', 'You have gone over your budget for this month.');
        }

        if ($overview['isLow']) {
            return back()->with('warning', 'Low balance — you have used most of this month\'s budget.');
        }

        return back()->with('status', 'Expense recorded.');
    }

    public function destroyExpense(Request $request, Expense $expense): RedirectResponse
    {
        abort_unless($expense->user_id === $request->user()->id, 403);

        $expense->delete();

        return back()->with('status', 'Expense removed.');
    }

    /**
     * Generates the end-of-month AI review. Only available once the month has
     * finished so the summary covers a complete period.
     */
    public function generateSummary(Request $request, SpendingAdvisor $advisor): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'month' => ['required', 'date_format:Y-m'],
        ]);

        $month = Carbon::createFromFormat('Y-m', $validated['month'])->startOfMonth();

        if ($month->greaterThanOrEqualTo(now()->startOfMonth())) {
            return back()->with('error', 'The monthly summary is available once the month is over.');
        }

        $expenses = $this->budgets->expensesForMonth($user, $month);

        if ($expenses->isEmpty()) {
            return back()->with('error', 'There is no spending recorded for '.$month->format('F Y').'.');
        }

        $summary = $advisor->summarise($user, $month, $this->budgets->overview($user, $month), $expenses);

        $user->expenseSummaries()->updateOrCreate(
            ['month' => $month->toDateString()],
            [
                'summary' => $summary->summary,
                'tips' => $summary->tips,
                'generated_by' => $summary->generatedBy,
            ],
        );

        return redirect()
            ->route('money.index', ['month' => $month->format('Y-m')])
            ->with('status', 'Summary ready for '.$month->format('F Y').'.');
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
