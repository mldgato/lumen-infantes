<?php

namespace App\Livewire\Profesor\Reports;

use App\Models\Classroom;
use App\Models\ClassroomCourseAssignment;
use App\Models\GradeBook;
use App\Models\GradeBookScore;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MissingActivities extends Component
{
    public bool $readyToLoad = false;

    public string $filterClassroom  = '';
    public string $filterCourse     = '';
    public string $filterUnit       = '';

    public array $activities  = [];
    public array $reportData  = [];
    public bool  $generated   = false;
    public string $courseName = '';

    public function loadData(): void
    {
        $this->readyToLoad = true;
    }

    public function updatedFilterClassroom(): void
    {
        $this->filterCourse = '';
        $this->filterUnit   = '';
        $this->resetReport();
    }

    public function updatedFilterCourse(): void
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
        $this->validate([
            'filterClassroom' => 'required',
            'filterCourse'    => 'required',
            'filterUnit'      => 'required',
        ], [
            'filterClassroom.required' => 'Seleccione un aula.',
            'filterCourse.required'    => 'Seleccione un curso.',
            'filterUnit.required'      => 'Seleccione una unidad.',
        ]);

        $professor = Auth::user()->professor;

        $assignment = ClassroomCourseAssignment::with(['pensumCourse.course'])
            ->where('classroom_id', $this->filterClassroom)
            ->where('pensum_course_id', $this->filterCourse)
            ->where('unit', $this->filterUnit)
            ->where('professor_id', $professor->id)
            ->first();

        if (! $assignment) {
            $this->dispatch('showAlert', ['type' => 'warning', 'message' => 'No se encontró el cuadro para los filtros seleccionados.']);
            return;
        }

        $this->courseName = $assignment->pensumCourse->course->course_name;

        $gradeBook = GradeBook::where('classroom_course_assignment_id', $assignment->id)->first();

        if (! $gradeBook) {
            $this->dispatch('showAlert', ['type' => 'warning', 'message' => 'No existe un cuadro de calificaciones para este curso y unidad.']);
            return;
        }

        $activitiesCollection = $gradeBook->activities()->with('activityType')->get();
        $this->activities = $activitiesCollection->map(fn($a) => [
            'id'   => $a->id,
            'name' => $a->name,
            'type' => $a->activityType->name ?? '',
        ])->toArray();

        $activityIds = $activitiesCollection->pluck('id');

        $scoresByStudent = GradeBookScore::whereIn('grade_book_activity_id', $activityIds)
            ->get()
            ->groupBy('student_id')
            ->map(fn($group) => $group->keyBy('grade_book_activity_id'));

        // CONSULTA DE ESTUDIANTES ACTUALIZADA
        $students = Student::whereHas(
            'enrollments',
            fn($q) => $q->where('classroom_id', $this->filterClassroom)->where('status', 'Activo')
        )
            ->join('users', 'students.user_id', '=', 'users.id')
            ->select('students.*')
            ->orderBy('users.surname')
            ->orderBy('users.second_surname')
            ->orderBy('users.first_name')
            ->orderBy('users.middle_name')
            ->with('user')
            ->get();

        $this->reportData = $students->values()->map(function ($student, $idx) use ($activitiesCollection, $scoresByStudent) {
            $studentScores = $scoresByStudent->get($student->id, collect());
            $results       = [];
            $missingCount  = 0;

            foreach ($activitiesCollection as $activity) {
                $score   = $studentScores->get($activity->id);
                $missing = $score === null || $score->score === null || (float) $score->score === 0.0;
                $results[$activity->id] = ! $missing;
                if ($missing) $missingCount++;
            }

            return [
                'clave'         => $idx + 1,
                'name'          => $student->user->full_full_name, // USO DEL ACCESSOR
                'results'       => $results,
                'missing_count' => $missingCount,
            ];
        })->toArray();

        $this->generated = true;
    }

    private function resetReport(): void
    {
        $this->activities  = [];
        $this->reportData  = [];
        $this->generated   = false;
        $this->courseName  = '';
    }

    public function render()
    {
        $year      = date('Y');
        $professor = Auth::user()->professor;

        $classrooms = collect();
        $courses    = collect();
        $units      = collect();

        if ($this->readyToLoad) {
            $classroomIds = ClassroomCourseAssignment::where('professor_id', $professor->id)
                ->whereHas('classroom', fn($q) => $q->where('year', $year))
                ->pluck('classroom_id')
                ->unique();

            $classrooms = Classroom::with(['grade', 'section'])
                ->whereIn('id', $classroomIds)
                ->orderBy('id')
                ->get();

            if ($this->filterClassroom) {
                $courses = ClassroomCourseAssignment::with(['pensumCourse.course'])
                    ->where('classroom_id', $this->filterClassroom)
                    ->where('professor_id', $professor->id)
                    ->get()
                    ->unique('pensum_course_id')
                    ->map(fn($a) => [
                        'id'   => $a->pensum_course_id,
                        'name' => $a->pensumCourse->course->course_name,
                    ])
                    ->values();
            }

            if ($this->filterClassroom && $this->filterCourse) {
                $units = ClassroomCourseAssignment::where('classroom_id', $this->filterClassroom)
                    ->where('pensum_course_id', $this->filterCourse)
                    ->where('professor_id', $professor->id)
                    ->pluck('unit')
                    ->sort()
                    ->values();
            }
        }

        return view('livewire.profesor.reports.missing-activities', compact('classrooms', 'courses', 'units'));
    }
}
