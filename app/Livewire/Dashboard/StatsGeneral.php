<?php

namespace App\Livewire\Dashboard;

use App\Models\Classroom;
use App\Models\Professor;
use App\Models\Student;
use Livewire\Component;

class StatsGeneral extends Component
{
    public bool $readyToLoad = false;

    public int $totalStudents = 0;

    public int $totalProfessors = 0;

    public int $totalClassrooms = 0;

    public function loadData(): void
    {
        $year = date('Y');

        $this->totalStudents = Student::whereHas(
            'enrollments',
            fn ($q) => $q->whereHas('classroom', fn ($q) => $q->where('year', $year))
                ->where('status', 'Activo')
        )->count();

        $this->totalProfessors = Professor::whereHas(
            'courseAssignments',
            fn ($q) => $q->whereHas('classroom', fn ($q) => $q->where('year', $year))
        )->count();

        $this->totalClassrooms = Classroom::where('year', $year)->count();

        $this->readyToLoad = true;
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.dashboard.stats-general');
    }
}
