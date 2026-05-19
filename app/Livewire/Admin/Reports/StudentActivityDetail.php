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

class StudentActivityDetail extends Component
{
    public bool $readyToLoad = false;

    public string $filterYear = '';

    public string $filterLevel = '';

    public string $filterGrade = '';

    public string $filterSection = '';

    public string $filterUnit = '';

    public array $studentList = [];

    public array $selectedStudentDetail = [];

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

        $assignments = ClassroomCourseAssignment::with(['gradeBook.activities'])
            ->where('classroom_id', $classroom->id)
            ->where('unit', $this->filterUnit)
            ->get();

        $totalActivities = 0;
        $scoreIndex = [];

        foreach ($assignments as $assignment) {
            $gradeBook = $assignment->gradeBook;
            $mainActivities = $gradeBook ? $gradeBook->activities->where('activity_type_id', 1) : collect();
            if (! $gradeBook || $mainActivities->isEmpty()) {
                continue;
            }

            $activityIds = $mainActivities->pluck('id');
            $totalActivities += $activityIds->count();

            $scores = GradeBookScore::whereIn('grade_book_activity_id', $activityIds)->get();
            foreach ($scores as $score) {
                $scoreIndex[$score->student_id][$score->grade_book_activity_id] = $score;
            }
        }

        $this->studentList = [];

        foreach ($students->values() as $idx => $student) {
            $done = 0;

            foreach ($assignments as $assignment) {
                $gradeBook = $assignment->gradeBook;
                $mainActivities = $gradeBook ? $gradeBook->activities->where('activity_type_id', 1) : collect();
                if (! $gradeBook || $mainActivities->isEmpty()) {
                    continue;
                }

                foreach ($mainActivities as $activity) {
                    $score = $scoreIndex[$student->id][$activity->id] ?? null;
                    if ($score !== null && $score->score !== null && (float) $score->score > 0) {
                        $done++;
                    }
                }
            }

            $this->studentList[] = [
                'number' => $idx + 1,
                'id' => $student->id,
                'name' => $student->user->full_full_name,
                'done' => $done,
                'total' => $totalActivities,
                'missing' => $totalActivities - $done,
                'classroom_id' => $classroom->id,
            ];
        }

        $this->generated = true;
    }

    public function loadStudentDetail(int $studentId): void
    {
        $userLevelIds = Auth::user()->levels()->pluck('levels.id');
        if (! $userLevelIds->contains((int) $this->filterLevel)) {
            abort(403);
        }

        $student = Student::with('user')->findOrFail($studentId);

        $classroom = Classroom::where('year', $this->filterYear)
            ->where('level_id', $this->filterLevel)
            ->where('grade_id', $this->filterGrade)
            ->where('section_id', $this->filterSection)
            ->firstOrFail();

        $assignments = ClassroomCourseAssignment::with([
            'pensumCourse.course',
            'professor.user',
            'gradeBook.activities.activityType',
        ])
            ->where('classroom_id', $classroom->id)
            ->where('unit', $this->filterUnit)
            ->get();

        $courses = [];

        foreach ($assignments as $assignment) {
            $courseName = $assignment->pensumCourse->course->course_name;
            $gradeBook = $assignment->gradeBook;

            $mainActivities = $gradeBook ? $gradeBook->activities->where('activity_type_id', 1) : collect();

            if (! $gradeBook || $mainActivities->isEmpty()) {
                $courses[] = [
                    'course_name' => $courseName,
                    'professor_name' => $assignment->professor->user->name ?? '—',
                    'has_activities' => false,
                    'activities' => [],
                    'done' => 0,
                    'total' => 0,
                ];

                continue;
            }

            $activityIds = $mainActivities->pluck('id');
            $studentScores = GradeBookScore::whereIn('grade_book_activity_id', $activityIds)
                ->where('student_id', $studentId)
                ->get()
                ->keyBy('grade_book_activity_id');

            $activities = [];
            $done = 0;

            foreach ($mainActivities as $activity) {
                $score = $studentScores->get($activity->id);
                $isDone = $score !== null && $score->score !== null && (float) $score->score > 0;
                if ($isDone) {
                    $done++;
                }
                $activities[] = [
                    'name' => $activity->name,
                    'type' => $activity->activityType->name ?? '',
                    'done' => $isDone,
                ];
            }

            $courses[] = [
                'course_name' => $courseName,
                'professor_name' => $assignment->professor->user->name ?? '—',
                'has_activities' => true,
                'activities' => $activities,
                'done' => $done,
                'total' => $gradeBook->activities->count(),
            ];
        }

        $this->selectedStudentDetail = [
            'student_id' => $studentId,
            'classroom_id' => $classroom->id,
            'name' => $student->user->full_full_name,
            'unit' => $this->filterUnit,
            'courses' => $courses,
        ];

        $this->dispatch('openStudentDetailModal');
    }

    private function resetReport(): void
    {
        $this->studentList = [];
        $this->selectedStudentDetail = [];
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

        return view('livewire.admin.reports.student-activity-detail', compact('years', 'levels', 'grades', 'sections', 'units'));
    }
}
