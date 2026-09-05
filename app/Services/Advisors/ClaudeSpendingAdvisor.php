<?php

namespace App\Services\Advisors;

use App\Contracts\SpendingAdvisor;
use App\Models\Expense;
use App\Models\User;
use App\Support\SpendingSummary;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Asks Claude to review a student's month of spending and suggest where they
 * could save. Any failure — network, refusal, malformed payload — falls back to
 * the local rule based advisor so the feature never breaks the page.
 */
class ClaudeSpendingAdvisor implements SpendingAdvisor
{
    public function __construct(private RuleBasedSpendingAdvisor $fallback) {}

    public function summarise(User $user, Carbon $month, array $overview, Collection $expenses): SpendingSummary
    {
        try {
            return $this->ask($month, $overview, $expenses);
        } catch (Throwable $exception) {
            Log::warning('Claude spending summary failed, using rule based advisor.', [
                'user_id' => $user->id,
                'month' => $month->toDateString(),
                'exception' => $exception->getMessage(),
            ]);

            return $this->fallback->summarise($user, $month, $overview, $expenses);
        }
    }

    /**
     * @param  array<string, mixed>  $overview
     * @param  Collection<int, Expense>  $expenses
     */
    private function ask(Carbon $month, array $overview, Collection $expenses): SpendingSummary
    {
        $response = Http::withHeaders([
            'x-api-key' => config('services.anthropic.key'),
            'anthropic-version' => config('services.anthropic.version'),
        ])
            ->timeout(config('services.anthropic.timeout'))
            ->retry(2, 500, throw: false)
            ->acceptJson()
            ->post(rtrim(config('services.anthropic.base_url'), '/').'/v1/messages', [
                'model' => config('services.anthropic.model'),
                'max_tokens' => 16000,
                'output_config' => [
                    'effort' => 'low',
                    'format' => [
                        'type' => 'json_schema',
                        'schema' => [
                            'type' => 'object',
                            'properties' => [
                                'summary' => [
                                    'type' => 'string',
                                    'description' => 'Two to four sentences reviewing the month of spending.',
                                ],
                                'tips' => [
                                    'type' => 'array',
                                    'description' => 'Two to four specific, actionable ways to save money next month.',
                                    'items' => ['type' => 'string'],
                                ],
                            ],
                            'required' => ['summary', 'tips'],
                            'additionalProperties' => false,
                        ],
                    ],
                ],
                'system' => 'You are a friendly financial coach for university students in Bangladesh. '
                    .'Amounts are in Bangladeshi Taka (BDT). Be concrete and encouraging, never preachy. '
                    .'Base every claim on the data you are given and do not invent transactions.',
                'messages' => [[
                    'role' => 'user',
                    'content' => $this->prompt($month, $overview, $expenses),
                ]],
            ])
            ->throw();

        $payload = $response->json();

        if (Arr::get($payload, 'stop_reason') === 'refusal') {
            throw new \RuntimeException('Claude declined to summarise this month.');
        }

        $text = collect(Arr::get($payload, 'content', []))
            ->firstWhere('type', 'text')['text'] ?? null;

        $decoded = json_decode((string) $text, true, flags: JSON_THROW_ON_ERROR);

        return new SpendingSummary(
            summary: (string) Arr::get($decoded, 'summary'),
            tips: array_values(array_filter((array) Arr::get($decoded, 'tips', []), 'is_string')),
            generatedBy: 'claude',
        );
    }

    /**
     * @param  array<string, mixed>  $overview
     * @param  Collection<int, Expense>  $expenses
     */
    private function prompt(Carbon $month, array $overview, Collection $expenses): string
    {
        $lines = $expenses
            ->map(fn (Expense $expense) => sprintf(
                '- %s (%s): %s on %s [%s]',
                $expense->spent_at->format('j M'),
                $expense->spent_at->format('D'),
                number_format((float) $expense->amount, 2),
                $expense->spent_on,
                $expense->category,
            ))
            ->implode(PHP_EOL);

        $categories = $overview['byCategory']
            ->map(fn (float $total, string $category) => sprintf('%s: %s', $category, number_format($total, 2)))
            ->implode(', ');

        return <<<PROMPT
        Review my spending for {$month->format('F Y')} and tell me how I did and where I could save.

        Monthly budget: {$this->money($overview['limit'])}
        Total spent: {$this->money($overview['spent'])}
        Remaining: {$this->money($overview['remaining'])}
        Average per day so far: {$this->money($overview['dailyAverage'])}
        Spending by category: {$categories}

        Every transaction:
        {$lines}
        PROMPT;
    }

    private function money(float $amount): string
    {
        return 'BDT '.number_format($amount, 2);
    }
}
