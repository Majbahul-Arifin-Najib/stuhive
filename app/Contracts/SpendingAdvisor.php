<?php

namespace App\Contracts;

use App\Models\Expense;
use App\Models\User;
use App\Support\SpendingSummary;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

interface SpendingAdvisor
{
    /**
     * Produce an end-of-month expenditure summary with savings advice.
     *
     * @param  array<string, mixed>  $overview  Output of BudgetService::overview()
     * @param  Collection<int, Expense>  $expenses
     */
    public function summarise(User $user, Carbon $month, array $overview, Collection $expenses): SpendingSummary;
}
