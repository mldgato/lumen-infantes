<?php

namespace App\Livewire\Admin\Reports;

use App\Models\Classroom;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ReportCards extends Component
{
    public string $filterYear = '';

    public string $filterLevel = '';

    public string $filterGrade = '';

    public string $filterSection = '';

    public string $filterUnit = '';

    public function updatedFilterYear(): void
    {
        $this->filterLevel = $this->filterGrade = $this->filterSection = $this->filterUnit = '';
    }

    public function updatedFilterLevel(): void
    {
        $this->filterGrade = $this->filterSection = $this->filterUnit = '';
    }

    public function updatedFilterGrade(): void
    {
        $this->filterSection = $this->filterUnit = '';
    }

    public function updatedFilterSection(): void
    {
        $this->filterUnit = '';
    }

    public function printAll(): void
    {
        $userLevelIds = Auth::user()->levels()->pluck('levels.id');
        if (! $userLevelIds->contains((int) $this->filterLevel)) {
            abort(403);
        }

        $this->validate([
            'filterYear' => 'required',
            'filterLevel' => 'required',
            'filterGrade' => 'required',
            'filterSection' => 'required',
            'filterUnit' => 'required',
        ], [
            'filterYear.required' => 'Seleccione un año.',
            'filterLevel.required' => 'Seleccione un nivel.',
            'filterGrade.required' => 'Seleccione un grado.',
            'filterSection.required' => 'Seleccione una sección.',
            'filterUnit.required' => 'Seleccione una unidad.',
        ]);

        $this->dispatch('openReportCardAll', [
            'url' => route('admin.reports.report-cards.all', [
                'year' => $this->filterYear,
                'level' => $this->filterLevel,
                'grade' => $this->filterGrade,
                'section' => $this->filterSection,
                'unit' => $this->filterUnit,
            ]),
        ]);
    }

    public function render()
    {
        $userLevelIds = Auth::user()->levels()->pluck('levels.id');

        $years = Classroom::select('year')->whereIn('level_id', $userLevelIds)->distinct()->orderByDesc('year')->pluck('year');
        $levels = Level::whereIn('id', $userLevelIds)->orderBy('level_name')->get();

        $grades = $this->filterLevel
            ? Grade::whereHas(
                'classrooms',
                fn ($q) => $q->where('level_id', $this->filterLevel)
                    ->when($this->filterYear, fn ($q) => $q->where('year', $this->filterYear))
            )->orderBy('ordering')->get()
            : collect();

        $sections = $this->filterGrade
            ? Section::whereHas(
                'classrooms',
                fn ($q) => $q->where('grade_id', $this->filterGrade)
                    ->where('level_id', $this->filterLevel)
                    ->when($this->filterYear, fn ($q) => $q->where('year', $this->filterYear))
            )->orderBy('section_name')->get()
            : collect();

        $units = collect();
        if ($this->filterSection && $this->filterSection !== 'all') {
            $classroom = Classroom::where('year', $this->filterYear)
                ->where('level_id', $this->filterLevel)
                ->where('grade_id', $this->filterGrade)
                ->where('section_id', $this->filterSection)
                ->first();

            if ($classroom) {
                $units = \App\Models\ClassroomCourseAssignment::where('classroom_id', $classroom->id)
                    ->distinct()->pluck('unit')->sort()->values();
            }
        } elseif ($this->filterSection === 'all' && $this->filterGrade) {
            $classroomIds = Classroom::where('year', $this->filterYear)
                ->where('level_id', $this->filterLevel)
                ->where('grade_id', $this->filterGrade)
                ->pluck('id');

            $units = \App\Models\ClassroomCourseAssignment::whereIn('classroom_id', $classroomIds)
                ->distinct()->pluck('unit')->sort()->values();
        }

        $studentList = collect();
        if ($this->filterYear && $this->filterLevel && $this->filterGrade && $this->filterSection && $this->filterUnit) {
            $classroomQuery = Classroom::where('year', $this->filterYear)
                ->where('level_id', $this->filterLevel)
                ->where('grade_id', $this->filterGrade);

            if ($this->filterSection !== 'all') {
                $classroomQuery->where('section_id', $this->filterSection);
            }

            $classrooms = $classroomQuery->with('section')->get();

            foreach ($classrooms as $classroom) {
                // CONSULTA CON FILTRO ACTIVO Y ORDEN COMPLETO
                $students = Student::whereHas(
                    'enrollments',
                    fn ($q) => $q->where('classroom_id', $classroom->id)->where('status', 'Activo')
                )
                    ->join('users', 'students.user_id', '=', 'users.id')
                    ->orderBy('users.surname')
                    ->orderBy('users.second_surname')
                    ->orderBy('users.first_name')
                    ->orderBy('users.middle_name')
                    ->select('students.*')
                    ->with('user')
                    ->get();

                foreach ($students as $idx => $student) {
                    $studentList->push([
                        'id' => $student->id,
                        'clave' => $idx + 1,
                        'name' => $student->user->full_full_name, // USO DEL ACCESSOR
                        'carnet' => $student->carnet ?? '—',
                        'section' => $classroom->section->section_name,
                        'classroom_id' => $classroom->id,
                        'url' => route('admin.reports.report-cards.student', [
                            'student_id' => $student->id,
                            'classroom_id' => $classroom->id,
                            'unit' => $this->filterUnit,
                        ]),
                    ]);
                }
            }
        }

        return view('livewire.admin.reports.report-cards', compact(
            'years',
            'levels',
            'grades',
            'sections',
            'units',
            'studentList'
        ));
    }
}
