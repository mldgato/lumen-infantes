<?php

namespace App\Livewire\Dashboard;

use App\Models\StudentEnrollment;
use Livewire\Component;

class StudentsByGradeChart extends Component
{
    public bool $readyToLoad = false;

    public array $studentsByGrade = [];

    public function loadData(): void
    {
        $year = date('Y');

        $this->studentsByGrade = StudentEnrollment::with('classroom.grade')
            ->where('status', 'Activo')
            ->whereHas('classroom', fn ($q) => $q->where('year', $year))
            ->get()
            ->groupBy(fn ($e) => $e->classroom->grade->grade_name ?? 'Sin grado')
            ->map(fn ($group) => $group->count())
            ->sortKeys()
            ->toArray();

        $this->readyToLoad = true;
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.dashboard.students-by-grade-chart');
    }
}
