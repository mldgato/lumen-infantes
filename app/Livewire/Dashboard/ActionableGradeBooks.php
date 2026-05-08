<?php

namespace App\Livewire\Dashboard;

use App\Models\GradeBook;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ActionableGradeBooks extends Component
{
    public bool $readyToLoad = false;

    public array $actionableGradeBooks = [];

    public function loadData(): void
    {
        $year = date('Y');
        $professor = Auth::user()->professor;

        if (! $professor) {
            $this->readyToLoad = true;

            return;
        }

        $this->actionableGradeBooks = GradeBook::with([
            'assignment.classroom.grade',
            'assignment.classroom.section',
            'assignment.pensumCourse.course',
        ])
            ->whereIn('status', ['open', 'rejected'])
            ->whereHas(
                'assignment',
                fn ($q) => $q->where('professor_id', $professor->id)
                    ->whereHas('classroom', fn ($q) => $q->where('year', $year))
            )
            ->orderBy('status')
            ->get()
            ->map(fn ($gb) => [
                'grade' => $gb->assignment->classroom->grade->grade_name,
                'section' => $gb->assignment->classroom->section->section_name,
                'course' => $gb->assignment->pensumCourse->course->course_name,
                'unit' => $gb->assignment->unit,
                'status' => $gb->status,
                'reason' => $gb->rejection_reason,
            ])
            ->toArray();

        $this->readyToLoad = true;
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.dashboard.actionable-grade-books');
    }
}
