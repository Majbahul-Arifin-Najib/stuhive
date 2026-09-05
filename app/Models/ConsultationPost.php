<?php

namespace App\Models;

use App\Models\Concerns\BelongsToPost;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

#[Fillable([
    'post_id', 'course_code', 'consultation_day', 'consultation_date',
    'consultation_time', 'room', 'capacity', 'postponed_at', 'postpone_reason',
])]
class ConsultationPost extends Model
{
    use BelongsToPost, HasFactory;

    protected $attributes = [
        'capacity' => 10,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'consultation_date' => 'date',
            'consultation_time' => 'string',
            'postponed_at' => 'datetime',
            'capacity' => 'integer',
        ];
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(ConsultationBooking::class, 'post_id', 'post_id');
    }

    public function startsAt(): Carbon
    {
        return $this->consultation_date->copy()->setTimeFromTimeString($this->consultation_time);
    }

    public function isFull(): bool
    {
        return $this->bookings()->count() >= $this->capacity;
    }

    public function wasPostponed(): bool
    {
        return $this->postponed_at !== null;
    }
}
