<?php

namespace App\Livewire\Dashboard;

use App\Models\Classroom;
use App\Models\ClassroomCourseAssignment;
use App\Models\GradeBook;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ProfesorGradeBooksChart extends Component
{
    public bool $readyToLoad = false;

    public array $gradeBookStatusByClassroom = [];

    public function loadData(): void
    {
        $year = date('Y');
        $professor = Auth::user()->professor;

        $classroomIds = ClassroomCourseAssignment::where('professor_id', $professor->id)
            ->whereHas('classroom', fn ($q) => $q->where('year', $year))
            ->pluck('classroom_id')
            ->unique();

        $classrooms = Classroom::with(['grade', 'section'])
            ->whereIn('id', $classroomIds)
            ->get();

        $chartData = [];
        foreach ($classrooms as $classroom) {
            $label = $classroom->grade->grade_name.' '.$classroom->section->section_name;
            $statuses = GradeBook::whereHas(
                'assignment',
                fn ($q) => $q->where('professor_id', $professor->id)
                    ->where('classroom_id', $classroom->id)
            )
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray();

            $chartData[] = [
                'label' => $label,
                'open' => $statuses['open'] ?? 0,
                'locked' => $statuses['locked'] ?? 0,
                'approved' => $statuses['approved'] ?? 0,
                'rejected' => $statuses['rejected'] ?? 0,
            ];
        }

        $this->gradeBookStatusByClassroom = $chartData;
        $this->readyToLoad = true;
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.dashboard.profesor-grade-books-chart');
    }
}
