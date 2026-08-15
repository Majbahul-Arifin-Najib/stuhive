<?php

namespace App\Providers;

use App\Contracts\SpendingAdvisor;
use App\Services\Advisors\ClaudeSpendingAdvisor;
use App\Services\Advisors\RuleBasedSpendingAdvisor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(SpendingAdvisor::class, fn () => filled(config('services.anthropic.key'))
            ? $this->app->make(ClaudeSpendingAdvisor::class)
            : $this->app->make(RuleBasedSpendingAdvisor::class));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventSilentlyDiscardingAttributes($this->app->isLocal());
    }
}
