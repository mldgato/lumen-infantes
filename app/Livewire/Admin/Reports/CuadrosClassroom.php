<?php

namespace App\Livewire\Admin\Reports;

use App\Models\Classroom;
use App\Models\ClassroomCourseAssignment;
use App\Models\Grade;
use App\Models\GradeBook;
use App\Models\Level;
use App\Models\Section;
use Livewire\Component;

class CuadrosClassroom extends Component
{
    public string $filterYear    = '';
    public string $filterLevel   = '';
    public string $filterGrade   = '';
    public string $filterSection = '';
    public string $filterUnit    = '';

    public array $approvedGradeBooks = [];

    public int $approvedCount = 0;

    public function updatedFilterYear(): void
    {
        $this->filterLevel = $this->filterGrade = $this->filterSection = $this->filterUnit = '';
        $this->approvedCount = 0;
        $this->approvedGradeBooks = [];
    }

    public function updatedFilterLevel(): void
    {
        $this->filterGrade = $this->filterSection = $this->filterUnit = '';
        $this->approvedCount = 0;
        $this->approvedGradeBooks = [];
    }

    public function updatedFilterGrade(): void
    {
        $this->filterSection = $this->filterUnit = '';
        $this->approvedCount = 0;
        $this->approvedGradeBooks = [];
    }

    public function updatedFilterSection(): void
    {
        $this->filterUnit = '';
        $this->approvedCount = 0;
        $this->approvedGradeBooks = [];
    }

    public function updatedFilterUnit(): void
    {
        $this->countApproved();
        $this->loadGradeBooks();
    }

    protected function countApproved(): void
    {
        if (! $this->filterYear || ! $this->filterLevel || ! $this->filterGrade || ! $this->filterSection || ! $this->filterUnit) {
            $this->approvedCount = 0;
            return;
        }

        $classroom = Classroom::where('year', $this->filterYear)
            ->where('level_id', $this->filterLevel)
            ->where('grade_id', $this->filterGrade)
            ->where('section_id', $this->filterSection)
            ->first();

        if (! $classroom) {
            $this->approvedCount = 0;
            return;
        }

        $this->approvedCount = GradeBook::where('status', 'approved')
            ->whereHas(
                'assignment',
                fn($q) =>
                $q->where('classroom_id', $classroom->id)
                    ->where('unit', $this->filterUnit)
            )
            ->count();
    }

    public function download(): void
    {
        $this->validate([
            'filterYear'    => 'required',
            'filterLevel'   => 'required',
            'filterGrade'   => 'required',
            'filterSection' => 'required',
            'filterUnit'    => 'required',
        ], [
            'filterYear.required'    => 'Seleccione un año.',
            'filterLevel.required'   => 'Seleccione un nivel.',
            'filterGrade.required'   => 'Seleccione un grado.',
            'filterSection.required' => 'Seleccione una sección.',
            'filterUnit.required'    => 'Seleccione una unidad.',
        ]);

        $this->dispatch('downloadCuadros', [
            'url' => route('admin.reports.cuadros-classroom.download', [
                'year'    => $this->filterYear,
                'level'   => $this->filterLevel,
                'grade'   => $this->filterGrade,
                'section' => $this->filterSection,
                'unit'    => $this->filterUnit,
            ]),
        ]);
    }

    protected function loadGradeBooks(): void
    {
        if (! $this->filterYear || ! $this->filterLevel || ! $this->filterGrade || ! $this->filterSection || ! $this->filterUnit) {
            $this->approvedGradeBooks = [];
            return;
        }

        $classroom = Classroom::where('year', $this->filterYear)
            ->where('level_id', $this->filterLevel)
            ->where('grade_id', $this->filterGrade)
            ->where('section_id', $this->filterSection)
            ->first();

        if (! $classroom) {
            $this->approvedGradeBooks = [];
            return;
        }

        $this->approvedGradeBooks = GradeBook::with([
            'assignment.pensumCourse.course',
            'assignment.professor.user',
        ])
            ->where('status', 'approved')
            ->whereHas(
                'assignment',
                fn($q) =>
                $q->where('classroom_id', $classroom->id)
                    ->where('unit', $this->filterUnit)
            )
            ->get()
            ->map(fn($gb) => [
                'id'       => $gb->id,
                'curso'    => $gb->assignment->pensumCourse->course->course_name,
                'profesor' => $gb->assignment->professor->user->name,
                'view_url' => route('admin.reports.cuadros-classroom.view', $gb->id),
            ])
            ->toArray();
    }

    public function viewAll(): void
    {
        $this->validate([
            'filterYear'    => 'required',
            'filterLevel'   => 'required',
            'filterGrade'   => 'required',
            'filterSection' => 'required',
            'filterUnit'    => 'required',
        ], [
            'filterYear.required'    => 'Seleccione un año.',
            'filterLevel.required'   => 'Seleccione un nivel.',
            'filterGrade.required'   => 'Seleccione un grado.',
            'filterSection.required' => 'Seleccione una sección.',
            'filterUnit.required'    => 'Seleccione una unidad.',
        ]);

        $this->dispatch('viewAllCuadros', [
            'url' => route('admin.reports.cuadros-classroom.view-all', [
                'year'    => $this->filterYear,
                'level'   => $this->filterLevel,
                'grade'   => $this->filterGrade,
                'section' => $this->filterSection,
                'unit'    => $this->filterUnit,
            ]),
        ]);
    }

    public function render()
    {
        $years  = Classroom::select('year')->distinct()->orderByDesc('year')->pluck('year');
        $levels = Level::orderBy('level_name')->get();

        $grades = $this->filterLevel
            ? Grade::whereHas(
                'classrooms',
                fn($q) =>
                $q->where('level_id', $this->filterLevel)
                    ->when($this->filterYear, fn($q) => $q->where('year', $this->filterYear))
            )->orderBy('grade_name')->get()
            : collect();

        $sections = $this->filterGrade
            ? Section::whereHas(
                'classrooms',
                fn($q) =>
                $q->where('grade_id', $this->filterGrade)
                    ->where('level_id', $this->filterLevel)
                    ->when($this->filterYear, fn($q) => $q->where('year', $this->filterYear))
            )->orderBy('section_name')->get()
            : collect();

        $units = $this->filterSection
            ? ClassroomCourseAssignment::whereHas(
                'classroom',
                fn($q) =>
                $q->where('section_id', $this->filterSection)
                    ->where('grade_id', $this->filterGrade)
                    ->where('level_id', $this->filterLevel)
                    ->when($this->filterYear, fn($q) => $q->where('year', $this->filterYear))
            )->distinct()->pluck('unit')->sort()->values()
            : collect();

        return view('livewire.admin.reports.cuadros-classroom', compact(
            'years',
            'levels',
            'grades',
            'sections',
            'units'
        ));
    }
}
