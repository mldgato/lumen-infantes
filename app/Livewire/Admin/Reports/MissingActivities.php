<?php

namespace App\Livewire\Admin\Reports;

use App\Models\Classroom;
use App\Models\ClassroomCourseAssignment;
use App\Models\Grade;
use App\Models\GradeBookScore;
use App\Models\Level;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MissingActivities extends Component
{
    public bool $readyToLoad = false;

    public string $filterYear = '';

    public string $filterLevel = '';

    public string $filterGrade = '';

    public string $filterSection = '';

    public string $filterUnit = '';

    public array $coursesData = [];

    public bool $generated = false;

    public function loadData(): void
    {
        $this->readyToLoad = true;
    }

    public function updatedFilterYear(): void
    {
        $this->filterLevel = $this->filterGrade = $this->filterSection = $this->filterUnit = '';
        $this->resetReport();
    }

    public function updatedFilterLevel(): void
    {
        $this->filterGrade = $this->filterSection = $this->filterUnit = '';
        $this->resetReport();
    }

    public function updatedFilterGrade(): void
    {
        $this->filterSection = $this->filterUnit = '';
        $this->resetReport();
    }

    public function updatedFilterSection(): void
    {
        $this->filterUnit = '';
        $this->resetReport();
    }

    public function updatedFilterUnit(): void
    {
        $this->resetReport();
    }

    public function generateReport(): void
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

        $classroom = Classroom::where('year', $this->filterYear)
            ->where('level_id', $this->filterLevel)
            ->where('grade_id', $this->filterGrade)
            ->where('section_id', $this->filterSection)
            ->first();

        if (! $classroom) {
            $this->dispatch('showAlert', ['type' => 'warning', 'message' => 'No se encontró el aula para los filtros seleccionados.']);

            return;
        }

        // CONSULTA DE ESTUDIANTES ESTANDARIZADA
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

        $assignments = ClassroomCourseAssignment::with([
            'pensumCourse.course',
            'professor.user',
            'gradeBook.activities.activityType',
        ])
            ->where('classroom_id', $classroom->id)
            ->where('unit', $this->filterUnit)
            ->get();

        $this->coursesData = [];

        foreach ($assignments as $assignment) {
            $gradeBook = $assignment->gradeBook;
            if (! $gradeBook) {
                continue;
            }

            $activitiesCollection = $gradeBook->activities;
            if ($activitiesCollection->isEmpty()) {
                continue;
            }

            $activityIds = $activitiesCollection->pluck('id');

            $scoresByStudent = GradeBookScore::whereIn('grade_book_activity_id', $activityIds)
                ->get()
                ->groupBy('student_id')
                ->map(fn ($group) => $group->keyBy('grade_book_activity_id'));

            $activitiesArr = $activitiesCollection->map(fn ($a) => [
                'id' => $a->id,
                'name' => $a->name,
                'type' => $a->activityType->name ?? '',
            ])->toArray();

            $studentsArr = $students->values()->map(function ($student, $idx) use ($activitiesCollection, $scoresByStudent) {
                $studentScores = $scoresByStudent->get($student->id, collect());
                $results = [];
                $missingCount = 0;

                foreach ($activitiesCollection as $activity) {
                    $score = $studentScores->get($activity->id);
                    $missing = $score === null || $score->score === null || (float) $score->score === 0.0;
                    $results[$activity->id] = ! $missing;
                    if ($missing) {
                        $missingCount++;
                    }
                }

                return [
                    'clave' => $idx + 1,
                    'name' => $student->user->full_full_name, // USO DEL ACCESSOR
                    'results' => $results,
                    'missing_count' => $missingCount,
                ];
            })->toArray();

            $this->coursesData[] = [
                'course_name' => $assignment->pensumCourse->course->course_name,
                'professor_name' => $assignment->professor->user->name ?? '—',
                'status' => $gradeBook->status,
                'activities' => $activitiesArr,
                'students' => $studentsArr,
            ];
        }

        $this->generated = true;
    }

    private function resetReport(): void
    {
        $this->coursesData = [];
        $this->generated = false;
    }

    public function render()
    {
        $userLevelIds = Auth::user()->levels()->pluck('levels.id');

        $years = Classroom::select('year')->whereIn('level_id', $userLevelIds)->distinct()->orderByDesc('year')->pluck('year');
        $levels = Level::whereIn('id', $userLevelIds)->orderBy('level_name')->get();
        $grades = $this->filterLevel
            ? Grade::whereHas('classrooms', fn ($q) => $q->where('level_id', $this->filterLevel)->when($this->filterYear, fn ($q) => $q->where('year', $this->filterYear)))->orderBy('ordering')->get()
            : collect();
        $sections = $this->filterGrade
            ? Section::whereHas('classrooms', fn ($q) => $q->where('grade_id', $this->filterGrade)->where('level_id', $this->filterLevel)->when($this->filterYear, fn ($q) => $q->where('year', $this->filterYear)))->orderBy('section_name')->get()
            : collect();
        $units = collect();

        if ($this->filterSection) {
            $classroom = Classroom::where('year', $this->filterYear)
                ->where('level_id', $this->filterLevel)
                ->where('grade_id', $this->filterGrade)
                ->where('section_id', $this->filterSection)
                ->first();

            if ($classroom) {
                $units = ClassroomCourseAssignment::where('classroom_id', $classroom->id)
                    ->distinct()->pluck('unit')->sort()->values();
            }
        }

        return view('livewire.admin.reports.missing-activities', compact('years', 'levels', 'grades', 'sections', 'units'));
    }
}
