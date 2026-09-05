<?php

namespace App\Services\Advisors;

use App\Contracts\SpendingAdvisor;
use App\Models\Expense;
use App\Models\User;
use App\Support\SpendingSummary;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Number;

/**
 * Offline fallback used when no Claude API key is configured, or when the API
 * call fails. Produces the same shape of advice from simple arithmetic.
 */
class RuleBasedSpendingAdvisor implements SpendingAdvisor
{
    public function summarise(User $user, Carbon $month, array $overview, Collection $expenses): SpendingSummary
    {
        $limit = $overview['limit'];
        $spent = $overview['spent'];
        $byCategory = $overview['byCategory'];
        $topCategory = $byCategory->keys()->first();
        $topAmount = $byCategory->first() ?? 0.0;

        $lines = [
            sprintf(
                'In %s you spent %s across %d %s.',
                $month->format('F Y'),
                Number::currency($spent, 'BDT'),
                $expenses->count(),
                str('entry')->plural($expenses->count()),
            ),
        ];

        if ($limit > 0) {
            $lines[] = $spent > $limit
                ? sprintf('That is %s over your %s budget.', Number::currency($spent - $limit, 'BDT'), Number::currency($limit, 'BDT'))
                : sprintf('You stayed %s under your %s budget.', Number::currency($limit - $spent, 'BDT'), Number::currency($limit, 'BDT'));
        }

        if ($topCategory) {
            $share = $spent > 0 ? round($topAmount / $spent * 100) : 0;
            $lines[] = sprintf('Your biggest category was %s at %s (%d%% of everything you spent).', $topCategory, Number::currency($topAmount, 'BDT'), $share);
        }

        $lines[] = sprintf('That works out to about %s a day.', Number::currency($overview['dailyAverage'], 'BDT'));

        return new SpendingSummary(
            summary: implode(' ', $lines),
            tips: $this->tips($overview, $expenses),
            generatedBy: 'rules',
        );
    }

    /**
     * @param  array<string, mixed>  $overview
     * @param  Collection<int, Expense>  $expenses
     * @return array<int, string>
     */
    private function tips(array $overview, Collection $expenses): array
    {
        $tips = [];
        $byCategory = $overview['byCategory'];
        $spent = $overview['spent'];
        $limit = $overview['limit'];

        if ($topCategory = $byCategory->keys()->first()) {
            $tips[] = sprintf(
                'Trimming %s by 15%% would save you about %s next month.',
                $topCategory,
                Number::currency($byCategory->first() * 0.15, 'BDT'),
            );
        }

        $small = $expenses->filter(fn ($expense) => (float) $expense->amount <= 100);

        if ($small->count() >= 5) {
            $tips[] = sprintf(
                'You logged %d small purchases under ৳100 totalling %s — those add up faster than the big ones.',
                $small->count(),
                Number::currency((float) $small->sum('amount'), 'BDT'),
            );
        }

        if ($limit > 0 && $spent > $limit) {
            $tips[] = sprintf(
                'Set next month\'s budget to at least %s, or plan where to cut before the month starts.',
                Number::currency($spent, 'BDT'),
            );
        } elseif ($limit > 0) {
            $tips[] = 'You came in under budget — move the leftover into savings before it gets spent.';
        } else {
            $tips[] = 'Set a monthly budget so StuHive can warn you before you overspend.';
        }

        return $tips;
    }
}
