<?php

namespace App\Livewire\Admin;

use App\Models\Classroom;
use App\Models\GradeBook;
use App\Models\GradeChangeRequest;
use App\Models\Professor;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Livewire\Component;

class Dashboard extends Component
{
    public bool $readyToLoad = false;

    public int $totalStudents     = 0;
    public int $totalProfessors   = 0;
    public int $totalClassrooms   = 0;
    public int $pendingGradeBooks = 0;

    public array $studentsByGrade      = [];
    public array $gradeBookStatusChart = [];

    public array $recentPendingRequests = [];
    public array $recentGradeBooks      = [];

    public function loadData(): void
    {
        $year = date('Y');

        $this->totalStudents = Student::whereHas(
            'enrollments',
            fn($q) =>
            $q->whereHas('classroom', fn($q) => $q->where('year', $year))
                ->where('status', 'Activo')
        )->count();

        $this->totalProfessors = Professor::whereHas(
            'courseAssignments',
            fn($q) =>
            $q->whereHas('classroom', fn($q) => $q->where('year', $year))
        )->count();

        $this->totalClassrooms = Classroom::where('year', $year)->count();

        $this->pendingGradeBooks = GradeBook::where('status', 'locked')
            ->whereHas(
                'assignment',
                fn($q) =>
                $q->whereHas('classroom', fn($q) => $q->where('year', $year))
            )->count();

        $enrollments = StudentEnrollment::with('classroom.grade')
            ->where('status', 'Activo')
            ->whereHas('classroom', fn($q) => $q->where('year', $year))
            ->get()
            ->groupBy(fn($e) => $e->classroom->grade->grade_name ?? 'Sin grado');

        $this->studentsByGrade = $enrollments
            ->map(fn($group) => $group->count())
            ->sortKeys()
            ->toArray();

        $statuses = GradeBook::whereHas(
            'assignment',
            fn($q) =>
            $q->whereHas('classroom', fn($q) => $q->where('year', $year))
        )->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $this->gradeBookStatusChart = [
            'open'     => $statuses['open']     ?? 0,
            'locked'   => $statuses['locked']   ?? 0,
            'approved' => $statuses['approved'] ?? 0,
            'rejected' => $statuses['rejected'] ?? 0,
        ];

        $this->recentPendingRequests = GradeChangeRequest::with([
            'professor.user',
            'gradeBook.assignment.classroom.grade',
            'gradeBook.assignment.classroom.section',
            'gradeBook.assignment.pensumCourse.course',
        ])
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($r) => [
                'id'         => $r->id,
                'professor'  => $r->professor->user->name,
                'grade'      => $r->gradeBook->assignment->classroom->grade->grade_name,
                'section'    => $r->gradeBook->assignment->classroom->section->section_name,
                'course'     => $r->gradeBook->assignment->pensumCourse->course->course_name,
                'created_at' => $r->created_at->diffForHumans(),
            ])
            ->toArray();

        $this->recentGradeBooks = GradeBook::with([
            'assignment.classroom.grade',
            'assignment.classroom.section',
            'assignment.pensumCourse.course',
            'assignment.professor.user',
        ])
            ->where('status', 'locked')
            ->whereHas(
                'assignment',
                fn($q) =>
                $q->whereHas('classroom', fn($q) => $q->where('year', $year))
            )
            ->latest('updated_at')
            ->take(5)
            ->get()
            ->map(fn($gb) => [
                'grade'      => $gb->assignment->classroom->grade->grade_name,
                'section'    => $gb->assignment->classroom->section->section_name,
                'course'     => $gb->assignment->pensumCourse->course->course_name,
                'professor'  => $gb->assignment->professor->user->name,
                'unit'       => $gb->assignment->unit,
                'updated_at' => $gb->updated_at->diffForHumans(),
            ])
            ->toArray();

        $this->readyToLoad = true;
    }

    public function render()
    {
        return view('livewire.admin.dashboard');
    }
}
