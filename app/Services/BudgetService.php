<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class BudgetService
{
    /**
     * Budget position for a user in a given month.
     *
     * @return array{
     *     month: Carbon,
     *     budget: ?Budget,
     *     limit: float,
     *     spent: float,
     *     remaining: float,
     *     ratio: float,
     *     isLow: bool,
     *     isOver: bool,
     *     byCategory: Collection<string, float>,
     *     dailyAverage: float
     * }
     */
    public function overview(User $user, Carbon $month): array
    {
        $month = $month->copy()->startOfMonth();

        $budget = $user->budgets()->whereDate('month', $month->toDateString())->first();
        $limit = (float) ($budget->amount ?? 0);

        $expenses = $user->expenses()->forMonth($month)->get();
        $spent = (float) $expenses->sum('amount');
        $ratio = $limit > 0 ? $spent / $limit : 0.0;
        $elapsedDays = max(1, min($month->daysInMonth, (int) $month->copy()->startOfMonth()->diffInDays(now()) + 1));

        return [
            'month' => $month,
            'budget' => $budget,
            'limit' => $limit,
            'spent' => $spent,
            'remaining' => $limit - $spent,
            'ratio' => $ratio,
            'isLow' => $limit > 0 && $ratio >= (float) config('stuhive.low_balance_threshold') && $ratio < 1,
            'isOver' => $limit > 0 && $ratio >= 1,
            'byCategory' => $expenses
                ->groupBy('category')
                ->map(fn (Collection $group) => (float) $group->sum('amount'))
                ->sortDesc(),
            'dailyAverage' => round($spent / $elapsedDays, 2),
        ];
    }

    /**
     * The most recent month that has ended and still has spending recorded.
     */
    public function lastClosedMonth(User $user): ?Carbon
    {
        $latest = $user->expenses()
            ->whereDate('spent_at', '<', now()->startOfMonth()->toDateString())
            ->max('spent_at');

        return $latest ? Carbon::parse($latest)->startOfMonth() : null;
    }

    /**
     * @return Collection<int, Expense>
     */
    public function expensesForMonth(User $user, Carbon $month): Collection
    {
        return $user->expenses()
            ->forMonth($month)
            ->orderByDesc('spent_at')
            ->orderByDesc('id')
            ->get();
    }
}
