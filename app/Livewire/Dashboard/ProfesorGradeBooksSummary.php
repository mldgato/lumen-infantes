<?php

namespace App\Livewire\Dashboard;

use App\Models\ClassroomCourseAssignment;
use App\Models\GradeBook;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ProfesorGradeBooksSummary extends Component
{
    public bool $readyToLoad = false;

    public int $totalOpen = 0;

    public int $totalLocked = 0;

    public int $totalApproved = 0;

    public int $totalRejected = 0;

    public function loadData(): void
    {
        $year = date('Y');
        $professor = Auth::user()->professor;

        $classroomIds = ClassroomCourseAssignment::where('professor_id', $professor->id)
            ->whereHas('classroom', fn ($q) => $q->where('year', $year))
            ->pluck('classroom_id')
            ->unique();

        $statuses = GradeBook::whereHas(
            'assignment',
            fn ($q) => $q->where('professor_id', $professor->id)
                ->whereIn('classroom_id', $classroomIds)
        )
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $this->totalOpen = $statuses['open'] ?? 0;
        $this->totalLocked = $statuses['locked'] ?? 0;
        $this->totalApproved = $statuses['approved'] ?? 0;
        $this->totalRejected = $statuses['rejected'] ?? 0;

        $this->readyToLoad = true;
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.dashboard.profesor-grade-books-summary');
    }
}
