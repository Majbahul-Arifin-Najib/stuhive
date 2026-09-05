<?php

namespace App\Notifications;

use App\Models\ConsultationPost;
use App\Models\User;
use Illuminate\Notifications\Notification;

class ConsultationBooked extends Notification
{
    public function __construct(
        private ConsultationPost $consultation,
        private User $student,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'consultation.booked',
            'title' => $this->student->name.' booked a consultation',
            'body' => $this->consultation->course_code.' on '
                .$this->consultation->consultation_date->format('j M').' at '
                .$this->consultation->startsAt()->format('g:i A'),
            'icon' => 'chat',
            'url' => route('consultations.index'),
        ];
    }
}
