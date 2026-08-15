<?php

namespace App\Support;

use Illuminate\Support\Carbon;

class CalendarEntry
{
    public function __construct(
        public string $title,
        public Carbon $startsAt,
        public string $kind,
        public ?string $detail = null,
        public ?string $url = null,
    ) {}

    public function dateKey(): string
    {
        return $this->startsAt->toDateString();
    }

    public function time(): string
    {
        return $this->startsAt->format('g:i A');
    }

    public function toneClasses(): string
    {
        return match ($this->kind) {
            'exam' => 'bg-rose-100 text-rose-800',
            'consultation' => 'bg-sky-100 text-sky-800',
            default => 'bg-hive-100 text-hive-800',
        };
    }

    public function dotClasses(): string
    {
        return match ($this->kind) {
            'exam' => 'bg-rose-500',
            'consultation' => 'bg-sky-500',
            default => 'bg-hive-500',
        };
    }
}
