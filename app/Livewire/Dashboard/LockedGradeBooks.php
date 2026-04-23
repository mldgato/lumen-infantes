<?php

namespace App\Livewire\Dashboard;

use App\Models\GradeBook;
use Livewire\Component;

class LockedGradeBooks extends Component
{
    public bool $readyToLoad = false;

    public array $recentLocked = [];

    public function loadData(): void
    {
        $year = date('Y');

        $this->recentLocked = GradeBook::with([
            'assignment.classroom.grade',
            'assignment.classroom.section',
            'assignment.pensumCourse.course',
            'assignment.professor.user',
        ])
            ->where('status', 'locked')
            ->whereHas(
                'assignment',
                fn ($q) => $q->whereHas('classroom', fn ($q) => $q->where('year', $year))
            )
            ->latest('updated_at')
            ->take(5)
            ->get()
            ->map(fn ($gb) => [
                'grade' => $gb->assignment->classroom->grade->grade_name,
                'section' => $gb->assignment->classroom->section->section_name,
                'course' => $gb->assignment->pensumCourse->course->course_name,
                'professor' => $gb->assignment->professor->user->name,
                'unit' => $gb->assignment->unit,
                'updated_at' => $gb->updated_at->diffForHumans(),
            ])
            ->toArray();

        $this->readyToLoad = true;
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.dashboard.locked-grade-books');
    }
}
