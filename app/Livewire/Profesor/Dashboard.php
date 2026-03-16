<?php

namespace App\Livewire\Profesor;

use App\Models\Classroom;
use App\Models\ClassroomCourseAssignment;
use App\Models\GradeBook;
use App\Models\GradeChangeRequest;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    public bool $readyToLoad = false;

    // KPIs
    public int $totalClassrooms       = 0;
    public int $approvedGradeBooks    = 0;
    public int $pendingGradeBooks     = 0;
    public int $pendingChangeRequests = 0;

    // Charts
    public array $gradeBookStatusByClassroom = [];

    // Action list
    public array $actionableGradeBooks = [];

    public function loadData(): void
    {
        $year      = date('Y');
        $professor = Auth::user()->professor;

        $classroomIds = ClassroomCourseAssignment::where('professor_id', $professor->id)
            ->whereHas('classroom', fn($q) => $q->where('year', $year))
            ->pluck('classroom_id')
            ->unique();

        $this->totalClassrooms = Classroom::whereIn('id', $classroomIds)->count();

        $this->approvedGradeBooks = GradeBook::where('status', 'approved')
            ->whereHas(
                'assignment',
                fn($q) =>
                $q->where('professor_id', $professor->id)
                    ->whereHas('classroom', fn($q) => $q->where('year', $year))
            )->count();

        $this->pendingGradeBooks = GradeBook::where('status', 'locked')
            ->whereHas(
                'assignment',
                fn($q) =>
                $q->where('professor_id', $professor->id)
                    ->whereHas('classroom', fn($q) => $q->where('year', $year))
            )->count();

        $this->pendingChangeRequests = GradeChangeRequest::where('professor_id', $professor->id)
            ->where('status', 'pending')
            ->count();

        // Chart: grade books status per classroom
        $classrooms = Classroom::with(['grade', 'section'])
            ->whereIn('id', $classroomIds)
            ->get();

        $chartData = [];
        foreach ($classrooms as $classroom) {
            $label    = $classroom->grade->grade_name . ' ' . $classroom->section->section_name;
            $statuses = GradeBook::whereHas(
                'assignment',
                fn($q) =>
                $q->where('professor_id', $professor->id)
                    ->where('classroom_id', $classroom->id)
            )
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray();

            $chartData[] = [
                'label'    => $label,
                'open'     => $statuses['open']     ?? 0,
                'locked'   => $statuses['locked']   ?? 0,
                'approved' => $statuses['approved'] ?? 0,
                'rejected' => $statuses['rejected'] ?? 0,
            ];
        }
        $this->gradeBookStatusByClassroom = $chartData;

        // Actionable grade books (open or rejected)
        $this->actionableGradeBooks = GradeBook::with([
            'assignment.classroom.grade',
            'assignment.classroom.section',
            'assignment.pensumCourse.course',
        ])
            ->whereIn('status', ['open', 'rejected'])
            ->whereHas(
                'assignment',
                fn($q) =>
                $q->where('professor_id', $professor->id)
                    ->whereHas('classroom', fn($q) => $q->where('year', $year))
            )
            ->orderBy('status')
            ->get()
            ->map(fn($gb) => [
                'grade'   => $gb->assignment->classroom->grade->grade_name,
                'section' => $gb->assignment->classroom->section->section_name,
                'course'  => $gb->assignment->pensumCourse->course->course_name,
                'unit'    => $gb->assignment->unit,
                'status'  => $gb->status,
                'reason'  => $gb->rejection_reason,
            ])
            ->toArray();

        $this->dispatch('profesorDashboardReady');
        $this->readyToLoad = true;
    }

    public function render()
    {
        return view('livewire.profesor.dashboard');
    }
}
