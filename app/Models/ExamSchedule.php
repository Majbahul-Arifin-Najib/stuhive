<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

#[Fillable(['course_code', 'section', 'day', 'exam_date', 'exam_time', 'room_number'])]
class ExamSchedule extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'exam_date' => 'date',
            'exam_time' => 'string',
        ];
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        $term = '%'.trim($term).'%';

        return $query->where(fn (Builder $inner) => $inner
            ->where('course_code', 'like', $term)
            ->orWhere('section', 'like', $term)
            ->orWhere('room_number', 'like', $term)
            ->orWhere('day', 'like', $term));
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->whereDate('exam_date', '>=', now()->toDateString());
    }

    public function startsAt(): Carbon
    {
        return $this->exam_date->copy()->setTimeFromTimeString($this->exam_time);
    }
}
