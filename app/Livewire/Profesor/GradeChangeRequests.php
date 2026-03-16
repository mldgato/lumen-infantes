<?php

namespace App\Livewire\Profesor;

use App\Models\AcademicConfiguration;
use App\Models\GradeBook;
use App\Models\GradeBookActivity;
use App\Models\GradeBookScore;
use App\Models\GradeBookTotal;
use App\Models\GradeChangeRequest;
use App\Models\GradeChangeRequestItem;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class GradeChangeRequests extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public bool $readyToLoad = false;
    public string $search    = '';

    // Step 1: select grade book
    public ?GradeBook $selectedGradeBook = null;

    // Step 2: select students
    public array $selectedStudents = [];

    // Step 3: edit scores
    // scores[student_id][activity_id] = ['score' => x, 'improvement_score' => y]
    public array $scores = [];
    public string $reason = '';

    // View mode
    public string $view = 'list'; // list | select-students | edit-scores

    // Filters
    public string $filterLevel   = '';
    public string $filterGrade   = '';
    public string $filterSection = '';
    public string $filterUnit    = '';

    protected $queryString = [
        'search'        => ['except' => ''],
        'filterLevel'   => ['except' => ''],
        'filterGrade'   => ['except' => ''],
        'filterSection' => ['except' => ''],
        'filterUnit'    => ['except' => ''],
    ];

    public function loadRequests(): void
    {
        $this->readyToLoad = true;
    }

    public function updatedFilterLevel(): void
    {
        $this->filterGrade = $this->filterSection = $this->filterUnit = '';
        $this->resetPage();
    }

    public function updatedFilterGrade(): void
    {
        $this->filterSection = $this->filterUnit = '';
        $this->resetPage();
    }

    public function updatedFilterSection(): void
    {
        $this->filterUnit = '';
        $this->resetPage();
    }

    public function updatedFilterUnit(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    // ==========================================
    // STEP 1: SELECT GRADE BOOK
    // ==========================================

    public function selectGradeBook(int $gradeBookId): void
    {
        $this->selectedGradeBook = GradeBook::with([
            'assignment.classroom.level',
            'assignment.classroom.grade',
            'assignment.classroom.section',
            'assignment.pensumCourse.course',
            'activities.activityType',
            'activities.scores',
        ])->findOrFail($gradeBookId);

        $this->selectedStudents = [];
        $this->scores           = [];
        $this->reason           = '';
        $this->view             = 'select-students';
        $this->resetValidation();
    }

    // ==========================================
    // STEP 2: SELECT STUDENTS
    // ==========================================

    public function confirmStudents(): void
    {
        $this->validate([
            'selectedStudents' => 'required|array|min:1',
        ], [
            'selectedStudents.required' => 'Debe seleccionar al menos un estudiante.',
            'selectedStudents.min'      => 'Debe seleccionar al menos un estudiante.',
        ]);

        // Initialize scores with current values
        $this->scores = [];
        $activities   = $this->selectedGradeBook->activities;

        foreach ($this->selectedStudents as $studentId) {
            foreach ($activities as $activity) {
                $score = $activity->scores->firstWhere('student_id', $studentId);
                $this->scores[$studentId][$activity->id] = [
                    'score'             => $score ? (float) $score->score : 0,
                    'improvement_score' => $score ? $score->improvement_score : null,
                ];
            }
        }

        $this->view = 'edit-scores';
        $this->resetValidation();
    }

    // ==========================================
    // STEP 3: SUBMIT REQUEST
    // ==========================================

    public function submitRequest(): void
    {
        $this->authorize('profesor.grade-change-requests.create');

        $this->validate([
            'reason' => 'required|string|min:10|max:1000',
        ], [
            'reason.required' => 'El motivo del cambio es obligatorio.',
            'reason.min'      => 'El motivo debe tener al menos 10 caracteres.',
        ]);

        $professor  = Auth::user()->professor;
        $activities = $this->selectedGradeBook->activities;

        // Build items — only include changed scores
        $items = [];
        foreach ($this->selectedStudents as $studentId) {
            foreach ($activities as $activity) {
                $current = $activity->scores->firstWhere('student_id', $studentId);

                $oldScore       = $current ? (float) $current->score : 0;
                $oldImprovement = $current ? $current->improvement_score : null;
                $newScore       = (float) ($this->scores[$studentId][$activity->id]['score'] ?? 0);
                $newImprovement = $this->scores[$studentId][$activity->id]['improvement_score'] ?? null;
                $newImprovement = $newImprovement !== '' ? $newImprovement : null;

                $scoreChanged      = round($oldScore, 2) !== round($newScore, 2);
                $improvementChanged = round((float) $oldImprovement, 2) !== round((float) $newImprovement, 2);

                if ($scoreChanged || $improvementChanged) {
                    $items[] = [
                        'student_id'             => $studentId,
                        'grade_book_activity_id' => $activity->id,
                        'old_score'              => $oldScore,
                        'new_score'              => $newScore,
                        'old_improvement_score'  => $oldImprovement,
                        'new_improvement_score'  => $newImprovement,
                    ];
                }
            }
        }

        if (empty($items)) {
            $this->addError('reason', 'No se detectaron cambios en las calificaciones.');
            return;
        }

        DB::transaction(function () use ($professor, $items) {
            $request = GradeChangeRequest::create([
                'grade_book_id' => $this->selectedGradeBook->id,
                'professor_id'  => $professor->id,
                'reason'        => $this->reason,
                'status'        => 'pending',
            ]);

            foreach ($items as $item) {
                GradeChangeRequestItem::create(array_merge(
                    ['grade_change_request_id' => $request->id],
                    $item
                ));
            }
        });

        $this->reset(['selectedGradeBook', 'selectedStudents', 'scores', 'reason']);
        $this->view = 'list';

        $this->dispatch('showAlert', [
            'title'   => '¡Solicitud enviada!',
            'message' => 'La solicitud de cambio de notas fue enviada para revisión.',
            'type'    => 'success',
        ]);
    }

    public function cancelRequest(): void
    {
        $this->reset(['selectedGradeBook', 'selectedStudents', 'scores', 'reason']);
        $this->view = 'list';
        $this->resetValidation();
    }

    public function backToStudents(): void
    {
        $this->view = 'select-students';
        $this->resetValidation();
    }

    // ==========================================
    // HELPERS
    // ==========================================

    public function getStudents()
    {
        if (! $this->selectedGradeBook) return collect();

        return Student::whereHas(
            'enrollments',
            fn($q) =>
            $q->where('classroom_id', $this->selectedGradeBook->assignment->classroom_id)
                ->where('status', 'Activo')
        )
            ->join('users', 'students.user_id', '=', 'users.id')
            ->orderBy('users.surname')
            ->orderBy('users.second_surname')
            ->orderBy('users.first_name')
            ->orderBy('users.middle_name')
            ->select('students.*')
            ->with('user')
            ->get();
    }

    // Students blocked because they have a pending request for this grade book
    public function getBlockedStudentIds(): array
    {
        if (! $this->selectedGradeBook) return [];

        $pendingItems = GradeChangeRequestItem::whereHas(
            'request',
            fn($q) =>
            $q->where('grade_book_id', $this->selectedGradeBook->id)
                ->where('status', 'pending')
        )->pluck('student_id')->unique()->toArray();

        return $pendingItems;
    }

    public function render()
    {
        $professor = Auth::user()->professor;

        $assignedClassroomIds = \App\Models\ClassroomCourseAssignment::where('professor_id', $professor->id)
            ->whereHas('classroom', fn($q) => $q->where('year', date('Y')))
            ->pluck('classroom_id')
            ->unique();

        $levels = \App\Models\Level::whereHas(
            'classrooms',
            fn($q) =>
            $q->whereIn('id', $assignedClassroomIds)
        )->orderBy('level_name')->get();

        $grades = $this->filterLevel
            ? \App\Models\Grade::whereHas(
                'classrooms',
                fn($q) =>
                $q->whereIn('id', $assignedClassroomIds)
                    ->where('level_id', $this->filterLevel)
            )->orderBy('ordering')->get()
            : collect();

        $sections = $this->filterGrade
            ? \App\Models\Section::whereHas(
                'classrooms',
                fn($q) =>
                $q->whereIn('id', $assignedClassroomIds)
                    ->where('grade_id', $this->filterGrade)
                    ->where('level_id', $this->filterLevel)
            )->orderBy('section_name')->get()
            : collect();

        $units = $this->filterSection
            ? \App\Models\ClassroomCourseAssignment::where('professor_id', $professor->id)
            ->whereHas(
                'classroom',
                fn($q) =>
                $q->where('year', date('Y'))
                    ->where('section_id', $this->filterSection)
                    ->where('grade_id', $this->filterGrade)
                    ->where('level_id', $this->filterLevel)
            )
            ->distinct()
            ->pluck('unit')
            ->sort()
            ->values()
            : collect();

        $gradeBooks = $this->readyToLoad
            ? GradeBook::with([
                'assignment.classroom.level',
                'assignment.classroom.grade',
                'assignment.classroom.section',
                'assignment.pensumCourse.course',
            ])
            ->where('status', 'approved')
            ->whereHas(
                'assignment',
                fn($q) =>
                $q->where('professor_id', $professor->id)
                    ->whereHas(
                        'classroom',
                        fn($q) =>
                        $q->where('year', date('Y'))
                            ->when($this->filterLevel,   fn($q) => $q->where('level_id',   $this->filterLevel))
                            ->when($this->filterGrade,   fn($q) => $q->where('grade_id',   $this->filterGrade))
                            ->when($this->filterSection, fn($q) => $q->where('section_id', $this->filterSection))
                    )
                    ->when($this->filterUnit, fn($q) => $q->where('unit', $this->filterUnit))
            )
            ->where(function ($q) {
                $q->whereHas('assignment.classroom.grade', fn($q) =>
                $q->where('grade_name', 'like', '%' . $this->search . '%'))
                    ->orWhereHas('assignment.pensumCourse.course', fn($q) =>
                    $q->where('course_name', 'like', '%' . $this->search . '%'))
                    ->orWhereHas('assignment.classroom.section', fn($q) =>
                    $q->where('section_name', 'like', '%' . $this->search . '%'));
            })
            ->join('classroom_course_assignments', 'grade_books.classroom_course_assignment_id', '=', 'classroom_course_assignments.id')
            ->join('classrooms', 'classroom_course_assignments.classroom_id', '=', 'classrooms.id')
            ->join('levels', 'classrooms.level_id', '=', 'levels.id')
            ->join('grades', 'classrooms.grade_id', '=', 'grades.id')
            ->join('sections', 'classrooms.section_id', '=', 'sections.id')
            ->join('pensum_courses', 'classroom_course_assignments.pensum_course_id', '=', 'pensum_courses.id')
            ->join('courses', 'pensum_courses.course_id', '=', 'courses.id')
            ->orderBy('levels.level_name')
            ->orderBy('grades.ordering')
            ->orderBy('sections.section_name')
            ->orderBy('courses.course_name')
            ->orderBy('classroom_course_assignments.unit')
            ->select('grade_books.*')
            ->paginate(10)
            : [];

        // My submitted requests
        $myRequests = $this->readyToLoad
            ? GradeChangeRequest::with([
                'gradeBook.assignment.classroom.grade',
                'gradeBook.assignment.classroom.section',
                'gradeBook.assignment.pensumCourse.course',
                'items.student.user',
                'items.activity',
                'reviewer',
            ])
            ->where('professor_id', $professor->id)
            ->orderBy('created_at', 'desc')
            ->paginate(5)
            : [];

        $students       = $this->getStudents();
        $blockedIds     = $this->getBlockedStudentIds();
        $activities     = $this->selectedGradeBook?->activities ?? collect();
        $config         = $this->selectedGradeBook?->academicConfiguration;

        return view('livewire.profesor.grade-change-requests', compact(
            'gradeBooks',
            'myRequests',
            'students',
            'blockedIds',
            'activities',
            'config',
            'levels',
            'grades',
            'sections',
            'units',
        ));
    }
}
