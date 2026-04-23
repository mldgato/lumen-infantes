<?php

namespace App\Livewire\Dashboard;

use App\Models\Classroom;
use App\Models\ClassroomCourseAssignment;
use App\Models\GradeBook;
use App\Models\GradeChangeRequest;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ProfesorStats extends Component
{
    public bool $readyToLoad = false;

    public int $totalClassrooms = 0;

    public int $approvedGradeBooks = 0;

    public int $pendingGradeBooks = 0;

    public int $pendingChangeRequests = 0;

    public function loadData(): void
    {
        $year = date('Y');
        $professor = Auth::user()->professor;

        $classroomIds = ClassroomCourseAssignment::where('professor_id', $professor->id)
            ->whereHas('classroom', fn ($q) => $q->where('year', $year))
            ->pluck('classroom_id')
            ->unique();

        $this->totalClassrooms = Classroom::whereIn('id', $classroomIds)->count();

        $this->approvedGradeBooks = GradeBook::where('status', 'approved')
            ->whereHas(
                'assignment',
                fn ($q) => $q->where('professor_id', $professor->id)
                    ->whereHas('classroom', fn ($q) => $q->where('year', $year))
            )->count();

        $this->pendingGradeBooks = GradeBook::where('status', 'locked')
            ->whereHas(
                'assignment',
                fn ($q) => $q->where('professor_id', $professor->id)
                    ->whereHas('classroom', fn ($q) => $q->where('year', $year))
            )->count();

        $this->pendingChangeRequests = GradeChangeRequest::where('professor_id', $professor->id)
            ->where('status', 'pending')
            ->count();

        $this->readyToLoad = true;
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.dashboard.profesor-stats');
    }
}
