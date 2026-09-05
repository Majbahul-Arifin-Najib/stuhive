<?php

namespace App\Notifications;

use App\Models\ConsultationPost;
use Illuminate\Notifications\Notification;

class ConsultationPostponed extends Notification
{
    public function __construct(private ConsultationPost $consultation) {}

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
            'kind' => 'consultation.postponed',
            'title' => 'Consultation rescheduled',
            'body' => $this->consultation->course_code.' moved to '
                .$this->consultation->consultation_date->format('j M').' at '
                .$this->consultation->startsAt()->format('g:i A')
                .($this->consultation->postpone_reason ? ' — '.$this->consultation->postpone_reason : ''),
            'icon' => 'clock',
            'url' => route('consultations.index'),
        ];
    }
}
