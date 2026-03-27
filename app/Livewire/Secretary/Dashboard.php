<?php

namespace App\Livewire\Secretary;

use App\Models\Classroom;
use App\Models\GradeBook;
use App\Models\GradeChangeRequest;
use App\Models\Professor;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

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

    public array $birthdayStudents  = [];
    public array $upcomingBirthdays = [];

    public function loadData(): void
    {
        $year  = date('Y');
        $month = now()->month;

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

        // Estudiantes cumpleañeros del mes con enrollment activo
        $this->birthdayStudents = User::role('Estudiante')
            ->whereNotNull('birthdate')
            ->whereMonth('birthdate', $month)
            ->whereHas(
                'student.enrollments',
                fn($q) =>
                $q->where('status', 'Activo')
                    ->whereHas('classroom', fn($q2) => $q2->where('year', $year))
            )
            ->orderByRaw('DAY(birthdate)')
            ->get()
            ->map(fn($u) => [
                'name'     => Str::limit($u->name, 20),
                'day'      => $u->birthdate->day,
                'age'      => now()->year - $u->birthdate->year,
                'initials' => $u->initials(),
                'image'    => $u->adminlte_image(),
                'is_today' => $u->birthdate->day === now()->day,
            ])
            ->toArray();

        // Próximos 4 cumpleaños del personal (sin Estudiante ni Super Administrador)
        $this->upcomingBirthdays = User::whereNotNull('birthdate')
            ->whereDoesntHave(
                'roles',
                fn($q) =>
                $q->whereIn('name', ['Estudiante', 'Super Administrador'])
            )
            ->selectRaw("*, DATEDIFF(
            DATE(CONCAT(
                IF(
                    DATE_FORMAT(birthdate, '%m-%d') >= DATE_FORMAT(NOW(), '%m-%d'),
                    YEAR(NOW()),
                    YEAR(NOW()) + 1
                ),
                '-',
                DATE_FORMAT(birthdate, '%m-%d')
            )),
            DATE(NOW())
        ) as days_until")
            ->orderBy('days_until')
            ->take(4)
            ->get()
            ->map(fn($u) => [
                'name'       => Str::limit($u->name, 24),
                'role'       => $u->roles()->first()?->name ?? 'Sin rol',
                'initials'   => $u->initials(),
                'image'      => $u->adminlte_image(),
                'day'        => $u->birthdate->day,
                'month'      => ucfirst(Carbon::parse($u->birthdate)->locale('es')->isoFormat('MMMM')),
                'days_until' => (int) $u->days_until,
                'is_today'   => (int) $u->days_until === 0,
            ])
            ->toArray();

        $this->readyToLoad = true;
    }

    public function render()
    {
        return view('livewire.secretary.dashboard');
    }
}
