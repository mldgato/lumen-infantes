<?php

namespace App\Notifications;

use App\Models\GradeChangeRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class GradeChangeRequestCreated extends Notification
{
    use Queueable;

    public function __construct(private readonly GradeChangeRequest $request) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $professor = $this->request->professor->user->full_full_name ?? '—';
        $assignment = $this->request->gradeBook->assignment;
        $course = $assignment->pensumCourse->course->course_name ?? '—';
        $grade = $assignment->classroom->grade->grade_name ?? '—';
        $section = $assignment->classroom->section->section_name ?? '—';
        $unit = $assignment->unit ?? '—';

        return [
            'icon' => 'fas fa-edit',
            'color' => 'info',
            'title' => 'Solicitud de cambio de notas',
            'message' => "{$professor} · {$course} · {$grade} {$section} · Unidad {$unit}",
            'url' => route('admin.grade-change-requests.index'),
        ];
    }
}
