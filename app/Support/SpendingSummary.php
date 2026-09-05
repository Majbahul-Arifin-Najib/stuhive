<?php

namespace App\Support;

class SpendingSummary
{
    /**
     * @param  array<int, string>  $tips
     */
    public function __construct(
        public string $summary,
        public array $tips = [],
        public string $generatedBy = 'rules',
    ) {}
}
