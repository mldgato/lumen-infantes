<?php

namespace App\Notifications;

use App\Models\GradeBook;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class GradeBookApproved extends Notification
{
    use Queueable;

    public function __construct(private readonly GradeBook $gradeBook) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $assignment = $this->gradeBook->assignment;
        $course = $assignment->pensumCourse->course->course_name ?? '—';
        $grade = $assignment->classroom->grade->grade_name ?? '—';
        $section = $assignment->classroom->section->section_name ?? '—';
        $unit = $assignment->unit ?? '—';

        return [
            'icon' => 'fas fa-check-circle',
            'color' => 'success',
            'title' => 'Cuadro aprobado',
            'message' => "{$course} · {$grade} {$section} · Unidad {$unit}",
            'url' => route('profesor.grade-books.index'),
        ];
    }
}
