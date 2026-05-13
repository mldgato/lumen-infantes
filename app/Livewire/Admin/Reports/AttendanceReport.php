<?php

namespace App\Livewire\Admin\Reports;

use App\Models\AttendanceRecord;
use App\Models\Classroom;
use App\Models\ClassroomCourseAssignment;
use App\Models\Grade;
use App\Models\Level;
use App\Models\PensumCourse;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class AttendanceReport extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $filterYear = '';

    public string $filterLevel = '';

    public string $filterGrade = '';

    public string $filterSection = '';

    public string $filterCourse = ''; // pensum_course_id

    public string $filterUnit = '';

    public ?int $assignmentId = null;

    // Modal PDF
    public bool $showPdfModal = false;

    public string $pdfFrom = '';

    public string $pdfTo = '';

    // ==========================================
    // CASCADA DE FILTROS
    // ==========================================

    public function updatedFilterYear(): void
    {
        $this->filterLevel = $this->filterGrade = $this->filterSection =
            $this->filterCourse = $this->filterUnit = '';
        $this->assignmentId = null;
        $this->resetPage();
    }

    public function updatedFilterLevel(): void
    {
        $this->filterGrade = $this->filterSection =
            $this->filterCourse = $this->filterUnit = '';
        $this->assignmentId = null;
        $this->resetPage();
    }

    public function updatedFilterGrade(): void
    {
        $this->filterSection = $this->filterCourse = $this->filterUnit = '';
        $this->assignmentId = null;
        $this->resetPage();
    }

    public function updatedFilterSection(): void
    {
        $this->filterCourse = $this->filterUnit = '';
        $this->assignmentId = null;
        $this->resetPage();
    }

    public function updatedFilterCourse(): void
    {
        $this->filterUnit = '';
        $this->assignmentId = null;
        $this->resetPage();
    }

    public function updatedFilterUnit(): void
    {
        $this->assignmentId = null;
        if ($this->filterUnit !== '') {
            $this->resolveAssignment();
        }
        $this->resetPage();
    }

    protected function resolveAssignment(): void
    {
        $classroom = Classroom::where('year', $this->filterYear)
            ->where('level_id', $this->filterLevel)
            ->where('grade_id', $this->filterGrade)
            ->where('section_id', $this->filterSection)
            ->first();

        if (! $classroom) {
            return;
        }

        $assignment = ClassroomCourseAssignment::where('classroom_id', $classroom->id)
            ->where('pensum_course_id', $this->filterCourse)
            ->where('unit', $this->filterUnit)
            ->first();

        $this->assignmentId = $assignment?->id;
    }

    // ==========================================
    // PDF
    // ==========================================

    public function openPdfModal(): void
    {
        $this->pdfFrom = now()->startOfMonth()->toDateString();
        $this->pdfTo = now()->toDateString();
        $this->showPdfModal = true;
        $this->resetValidation(['pdfFrom', 'pdfTo']);
    }

    public function closePdfModal(): void
    {
        $this->showPdfModal = false;
        $this->pdfFrom = '';
        $this->pdfTo = '';
    }

    public function downloadPdf(): void
    {
        $userLevelIds = Auth::user()->levels()->pluck('levels.id');
        if (! $userLevelIds->contains((int) $this->filterLevel)) {
            abort(403);
        }

        $this->validate([
            'pdfFrom' => 'required|date',
            'pdfTo' => 'required|date|after_or_equal:pdfFrom',
        ], [
            'pdfFrom.required' => 'La fecha inicial es obligatoria.',
            'pdfTo.required' => 'La fecha final es obligatoria.',
            'pdfTo.after_or_equal' => 'La fecha final debe ser igual o posterior a la inicial.',
        ]);

        $this->dispatch('downloadAttendancePdfAdmin', [
            'url' => route('admin.reports.attendance.pdf', [
                'assignment_id' => $this->assignmentId,
                'from' => $this->pdfFrom,
                'to' => $this->pdfTo,
            ]),
        ]);

        $this->closePdfModal();
    }

    // ==========================================
    // RENDER
    // ==========================================

    public function render()
    {
        $userLevelIds = Auth::user()->levels()->pluck('levels.id');

        $years = Classroom::select('year')->whereIn('level_id', $userLevelIds)->distinct()->orderByDesc('year')->pluck('year');

        $levels = $this->filterYear
            ? Level::whereIn('id', $userLevelIds)
                ->whereHas('classrooms', fn ($q) => $q->where('year', $this->filterYear))
                ->orderBy('level_name')->get()
            : collect();

        $grades = $this->filterLevel
            ? Grade::whereHas(
                'classrooms',
                fn ($q) => $q->where('year', $this->filterYear)->where('level_id', $this->filterLevel)
            )->orderBy('ordering')->get()
            : collect();

        $sections = $this->filterGrade
            ? Section::whereHas(
                'classrooms',
                fn ($q) => $q->where('year', $this->filterYear)
                    ->where('level_id', $this->filterLevel)
                    ->where('grade_id', $this->filterGrade)
            )->orderBy('section_name')->get()
            : collect();

        $pensumCourses = $this->filterSection
            ? PensumCourse::whereHas(
                'assignments.classroom',
                fn ($q) => $q->where('year', $this->filterYear)
                    ->where('level_id', $this->filterLevel)
                    ->where('grade_id', $this->filterGrade)
                    ->where('section_id', $this->filterSection)
            )->with('course')->orderBy('ordering')->get()
            : collect();

        $units = $this->filterCourse
            ? ClassroomCourseAssignment::whereHas(
                'classroom',
                fn ($q) => $q->where('year', $this->filterYear)
                    ->where('level_id', $this->filterLevel)
                    ->where('grade_id', $this->filterGrade)
                    ->where('section_id', $this->filterSection)
            )->where('pensum_course_id', $this->filterCourse)
                ->pluck('unit')->unique()->sort()->values()
            : collect();

        $assignment = $this->assignmentId
            ? ClassroomCourseAssignment::with([
                'classroom.level',
                'classroom.grade',
                'classroom.section',
                'pensumCourse.course',
                'professor.user',
            ])->find($this->assignmentId)
            : null;

        $attendanceRecords = $this->assignmentId
            ? AttendanceRecord::where('classroom_course_assignment_id', $this->assignmentId)
                ->with('entries')
                ->orderByDesc('date')
                ->paginate(15)
            : null;

        $totalStudents = ($this->assignmentId && $assignment)
            ? Student::whereHas(
                'enrollments',
                fn ($q) => $q->where('classroom_id', $assignment->classroom_id)->where('status', 'Activo')
            )->count()
            : 0;

        return view('livewire.admin.reports.attendance-report', compact(
            'years',
            'levels',
            'grades',
            'sections',
            'pensumCourses',
            'units',
            'assignment',
            'attendanceRecords',
            'totalStudents',
        ));
    }
}
