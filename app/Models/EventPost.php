<?php

namespace App\Models;

use App\Models\Concerns\BelongsToPost;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

#[Fillable(['post_id', 'event_name', 'club_name', 'venue', 'event_date', 'event_time', 'image_path'])]
class EventPost extends Model
{
    use BelongsToPost, HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'event_time' => 'string',
        ];
    }

    public function startsAt(): Carbon
    {
        return $this->event_date->copy()->setTimeFromTimeString($this->event_time);
    }
}
