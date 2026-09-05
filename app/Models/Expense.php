<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

#[Fillable(['user_id', 'spent_on', 'category', 'amount', 'spent_at'])]
class Expense extends Model
{
    use HasFactory;

    /**
     * Spending buckets offered when logging an expense.
     *
     * @var array<int, string>
     */
    public const CATEGORIES = ['food', 'transport', 'academic', 'housing', 'health', 'leisure', 'other'];

    protected $attributes = [
        'category' => 'other',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'spent_at' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForMonth(Builder $query, Carbon $month): Builder
    {
        return $query->whereBetween('spent_at', [
            $month->copy()->startOfMonth()->toDateString(),
            $month->copy()->endOfMonth()->toDateString(),
        ]);
    }
}
