<?php

namespace App\Livewire\Admin\Reports;

use App\Models\Classroom;
use App\Models\ClassroomCourseAssignment;
use App\Models\Professor;
use App\Models\Student;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ProfessorWorkload extends Component
{
    public string $filterYear = '';

    public bool $readyToLoad = false;

    public function updatedFilterYear(): void
    {
        $this->readyToLoad = false;
    }

    public function generateReport(): void
    {
        $this->validate(
            ['filterYear' => 'required'],
            ['filterYear.required' => 'Selecciona un año.']
        );

        $this->readyToLoad = true;
    }

    protected function computeWorkload(): Collection
    {
        $userLevelIds = Auth::user()->levels()->pluck('levels.id');

        $professors = Professor::with('user')
            ->join('users', 'professors.user_id', '=', 'users.id')
            ->whereHas(
                'courseAssignments.classroom',
                fn ($q) => $q->where('year', $this->filterYear)->whereIn('level_id', $userLevelIds)
            )
            ->select('professors.*')
            ->orderBy('users.surname')
            ->orderBy('users.first_name')
            ->get();

        return $professors->map(function (Professor $professor) use ($userLevelIds) {
            $assignments = ClassroomCourseAssignment::with('classroom')
                ->where('professor_id', $professor->id)
                ->whereHas('classroom', fn ($q) => $q->where('year', $this->filterYear)->whereIn('level_id', $userLevelIds))
                ->get();

            $classroomIds = $assignments->pluck('classroom_id')->unique();

            $studentCount = Student::whereHas(
                'enrollments',
                fn ($q) => $q->whereIn('classroom_id', $classroomIds)->where('status', 'Activo')
            )->count();

            return [
                'name' => $professor->user->full_full_name,
                'courses' => $assignments->count(),
                'classrooms' => $classroomIds->count(),
                'students' => $studentCount,
            ];
        });
    }

    public function render(): \Illuminate\View\View
    {
        $userLevelIds = Auth::user()->levels()->pluck('levels.id');

        $years = Classroom::select('year')
            ->whereIn('level_id', $userLevelIds)
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        $rows = collect();

        if ($this->readyToLoad && $this->filterYear) {
            $rows = $this->computeWorkload();
        }

        return view('livewire.admin.reports.professor-workload', compact('years', 'rows'));
    }
}
