<?php

namespace App\Notifications;

use App\Models\GradeChangeRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class GradeChangeRequestResolved extends Notification
{
    use Queueable;

    public function __construct(
        private readonly GradeChangeRequest $request,
        private readonly string $status,
        private readonly ?string $reason = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $assignment = $this->request->gradeBook->assignment;
        $course = $assignment->pensumCourse->course->course_name ?? '—';
        $grade = $assignment->classroom->grade->grade_name ?? '—';
        $section = $assignment->classroom->section->section_name ?? '—';
        $unit = $assignment->unit ?? '—';

        $approved = $this->status === 'approved';
        $message = "{$course} · {$grade} {$section} · Unidad {$unit}";

        if (! $approved && $this->reason) {
            $message .= ": {$this->reason}";
        }

        return [
            'icon' => $approved ? 'fas fa-check-circle' : 'fas fa-times-circle',
            'color' => $approved ? 'success' : 'danger',
            'title' => $approved ? 'Solicitud de cambio aprobada' : 'Solicitud de cambio rechazada',
            'message' => $message,
            'url' => route('profesor.grade-change-requests.index'),
        ];
    }
}
