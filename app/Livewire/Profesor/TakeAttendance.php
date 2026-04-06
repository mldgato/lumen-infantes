<?php

namespace App\Livewire\Profesor;

use App\Models\AttendanceEntry;
use App\Models\AttendanceRecord;
use App\Models\ClassroomCourseAssignment;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class TakeAttendance extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Lista de asignaciones
    public bool   $readyToLoad = false;
    public string $search      = '';

    // Asignación seleccionada
    public ?ClassroomCourseAssignment $assignment = null;

    // Formulario de asistencia
    public bool   $formLoaded      = false;
    public string $attendanceDate  = '';
    public ?int   $currentRecordId = null;
    public array  $entries         = []; // [student_id => bool]

    // Filtros del historial
    public string $historyMonth = '';
    public string $historyDate  = '';

    // Modal PDF
    public bool   $showPdfModal = false;
    public string $pdfFrom      = '';
    public string $pdfTo        = '';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    // ==========================================
    // INICIALIZACIÓN
    // ==========================================

    public function loadAssignments(): void
    {
        $this->readyToLoad = true;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingHistoryMonth(): void
    {
        $this->setPage(1, 'historyPage');
    }

    public function updatingHistoryDate(): void
    {
        $this->setPage(1, 'historyPage');
    }

    // ==========================================
    // ASIGNACIÓN
    // ==========================================

    public function openAssignment(int $assignmentId): void
    {
        $this->assignment = ClassroomCourseAssignment::with([
            'classroom.grade',
            'classroom.section',
            'classroom.level',
            'pensumCourse.course',
        ])->findOrFail($assignmentId);

        $this->resetAttendanceForm();
        $this->historyMonth = '';
        $this->historyDate  = '';
        $this->showPdfModal = false;
        $this->setPage(1, 'historyPage');
    }

    public function closeAssignment(): void
    {
        $this->assignment = null;
        $this->resetAttendanceForm();
        $this->showPdfModal = false;
    }

    protected function resetAttendanceForm(): void
    {
        $this->formLoaded      = false;
        $this->attendanceDate  = now()->toDateString();
        $this->currentRecordId = null;
        $this->entries         = [];
        $this->resetValidation();
    }

    // ==========================================
    // CARGAR / PREPARAR FORMULARIO
    // ==========================================

    public function loadDate(): void
    {
        $this->validate(
            ['attendanceDate' => 'required|date'],
            ['attendanceDate.required' => 'Seleccione una fecha.', 'attendanceDate.date' => 'Fecha inválida.']
        );

        $record = AttendanceRecord::where('classroom_course_assignment_id', $this->assignment->id)
            ->whereDate('date', $this->attendanceDate)
            ->with('entries')
            ->first();

        $this->loadEntriesFromRecord($record);
        $this->formLoaded = true;

        if ($record) {
            $this->dispatch('showAlert', [
                'title'   => 'Registro existente',
                'message' => 'Ya existe asistencia para esta fecha. Puedes editarla directamente.',
                'type'    => 'info',
            ]);
        }
    }

    public function editRecord(int $recordId): void
    {
        $record = AttendanceRecord::with('entries')->findOrFail($recordId);

        $this->attendanceDate = $record->date->toDateString();
        $this->loadEntriesFromRecord($record);
        $this->formLoaded = true;

        $this->dispatch('scrollToForm');
    }

    protected function loadEntriesFromRecord(?AttendanceRecord $record): void
    {
        $students = $this->getStudents();

        $stored = $record
            ? $record->entries->pluck('present', 'student_id')->map(fn($v) => (bool) $v)->toArray()
            : [];

        $this->currentRecordId = $record?->id;

        // Todos los estudiantes activos; los no registrados defaultean a presente
        $this->entries = $students->mapWithKeys(
            fn($s) => [$s->id => $stored[$s->id] ?? true]
        )->toArray();
    }

    public function clearForm(): void
    {
        $this->resetAttendanceForm();
    }

    // ==========================================
    // GUARDAR
    // ==========================================

    public function saveAttendance(): void
    {
        $this->validate(
            ['attendanceDate' => 'required|date'],
            ['attendanceDate.required' => 'La fecha es obligatoria.']
        );

        DB::beginTransaction();

        try {
            if ($this->currentRecordId) {
                $record = AttendanceRecord::findOrFail($this->currentRecordId);

                // Si la fecha cambió, verificar conflicto
                if ($record->date->toDateString() !== $this->attendanceDate) {
                    $conflict = AttendanceRecord::where('classroom_course_assignment_id', $this->assignment->id)
                        ->whereDate('date', $this->attendanceDate)
                        ->where('id', '!=', $record->id)
                        ->first();

                    if ($conflict) {
                        DB::rollBack();
                        $this->addError('attendanceDate', 'Ya existe un registro para esa fecha. Elige otra.');
                        return;
                    }
                }

                $record->update(['date' => $this->attendanceDate]);
            } else {
                // Guardia contra condición de carrera
                $existing = AttendanceRecord::where('classroom_course_assignment_id', $this->assignment->id)
                    ->whereDate('date', $this->attendanceDate)
                    ->first();

                if ($existing) {
                    DB::rollBack();
                    $this->dispatch('showAlert', [
                        'title'   => 'Fecha duplicada',
                        'message' => 'Ya existe un registro para esta fecha. Se ha cargado para edición.',
                        'type'    => 'warning',
                    ]);
                    $this->editRecord($existing->id);
                    return;
                }

                $record = AttendanceRecord::create([
                    'classroom_course_assignment_id' => $this->assignment->id,
                    'date'                           => $this->attendanceDate,
                ]);
                $this->currentRecordId = $record->id;
            }

            foreach ($this->entries as $studentId => $present) {
                AttendanceEntry::updateOrCreate(
                    ['attendance_record_id' => $record->id, 'student_id' => $studentId],
                    ['present'              => (bool) $present]
                );
            }

            DB::commit();

            $this->dispatch('toastMessage', [
                'type'    => 'success',
                'message' => 'Asistencia guardada correctamente.',
            ]);

            $this->setPage(1, 'historyPage');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('showAlert', [
                'title'   => 'Error',
                'message' => 'No se pudo guardar: ' . $e->getMessage(),
                'type'    => 'error',
            ]);
        }
    }

    // ==========================================
    // PDF
    // ==========================================

    public function openPdfModal(): void
    {
        $this->pdfFrom      = now()->startOfMonth()->toDateString();
        $this->pdfTo        = now()->toDateString();
        $this->showPdfModal = true;
        $this->resetValidation(['pdfFrom', 'pdfTo']);
    }

    public function closePdfModal(): void
    {
        $this->showPdfModal = false;
        $this->pdfFrom      = '';
        $this->pdfTo        = '';
    }

    public function downloadPdf(): void
    {
        $this->validate([
            'pdfFrom' => 'required|date',
            'pdfTo'   => 'required|date|after_or_equal:pdfFrom',
        ], [
            'pdfFrom.required'     => 'La fecha inicial es obligatoria.',
            'pdfTo.required'       => 'La fecha final es obligatoria.',
            'pdfTo.after_or_equal' => 'La fecha final debe ser igual o posterior a la inicial.',
        ]);

        $this->dispatch('downloadAttendancePdf', [
            'url' => route('profesor.attendance.pdf', [
                'assignment_id' => $this->assignment->id,
                'from'          => $this->pdfFrom,
                'to'            => $this->pdfTo,
            ]),
        ]);

        $this->closePdfModal();
    }

    // ==========================================
    // HELPERS
    // ==========================================

    public function getStudents()
    {
        return Student::whereHas('enrollments', function ($q) {
            $q->where('classroom_id', $this->assignment->classroom_id)
                ->where('status', 'Activo');
        })
            ->join('users', 'students.user_id', '=', 'users.id')
            ->select('students.*')
            ->orderBy('users.surname')
            ->orderBy('users.second_surname')
            ->orderBy('users.first_name')
            ->orderBy('users.middle_name')
            ->with('user')
            ->get();
    }

    // ==========================================
    // RENDER
    // ==========================================

    public function render()
    {
        $professor = Auth::user()->professor;

        $assignments = collect();

        if ($this->readyToLoad && !$this->assignment) {
            $assignments = ClassroomCourseAssignment::with([
                'classroom.level',
                'classroom.grade',
                'classroom.section',
                'pensumCourse.course',
            ])
                ->withCount('attendanceRecords')
                ->where('professor_id', $professor->id)
                ->whereHas('classroom', fn($q) => $q->where('year', date('Y')))
                ->where(function ($q) {
                    $q->whereHas('classroom.level', fn($q) => $q->where('level_name', 'like', '%' . $this->search . '%'))
                        ->orWhereHas('classroom.grade', fn($q) => $q->where('grade_name', 'like', '%' . $this->search . '%'))
                        ->orWhereHas('classroom.section', fn($q) => $q->where('section_name', 'like', '%' . $this->search . '%'))
                        ->orWhereHas('pensumCourse.course', fn($q) => $q->where('course_name', 'like', '%' . $this->search . '%'));
                })
                ->orderBy('classroom_id')
                ->orderBy('pensum_course_id')
                ->orderBy('unit')
                ->get()
                ->groupBy(fn($a) => $a->classroom_id . '-' . $a->pensum_course_id);
        }

        $students = $this->assignment ? $this->getStudents() : collect();

        $historyRecords = null;
        if ($this->assignment) {
            $historyRecords = AttendanceRecord::where('classroom_course_assignment_id', $this->assignment->id)
                ->with('entries')
                ->when($this->historyMonth, fn($q) => $q->whereMonth('date', $this->historyMonth))
                ->when($this->historyDate, fn($q) => $q->whereDate('date', $this->historyDate))
                ->orderByDesc('date')
                ->paginate(10, ['*'], 'historyPage');
        }

        return view('livewire.profesor.take-attendance', compact(
            'assignments',
            'students',
            'historyRecords',
        ));
    }
}
