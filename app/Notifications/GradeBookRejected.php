<?php

namespace App\Notifications;

use App\Models\GradeBook;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class GradeBookRejected extends Notification
{
    use Queueable;

    public function __construct(
        private readonly GradeBook $gradeBook,
        private readonly string $reason,
    ) {}

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
            'icon' => 'fas fa-times-circle',
            'color' => 'danger',
            'title' => 'Cuadro rechazado',
            'message' => "{$course} · {$grade} {$section} · Unidad {$unit}: {$this->reason}",
            'url' => route('profesor.grade-books.index'),
        ];
    }
}
