<?php

namespace App\Livewire\Students;

use App\Livewire\Forms\GuardianForm;
use App\Livewire\Forms\MedicalForm;
use App\Livewire\Forms\StudentForm;
use App\Livewire\Forms\UserForm;
use App\Models\AuditLog;
use App\Models\Classroom;
use App\Models\GradeBook;
use App\Models\GradeBookScore;
use App\Models\GradeBookTotal;
use App\Models\Guardian;
use App\Models\StudentEnrollment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class StudentList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public UserForm $userForm;

    public StudentForm $studentForm;

    public MedicalForm $medicalForm;

    public GuardianForm $guardianForm;

    public $search = '';

    public $sort = 'surname';

    public $direction = 'asc';

    public $cant = '10';

    public $readyToLoad = false;

    public $activeTab = 'general';

    // Variables para el Panel Familiar
    public $managingStudent = null;

    public $relationship_type = '';

    public $showGuardianForm = false; // Alterna entre ver la lista y ver el formulario

    // Inscripción
    public $enrollment_classroom_id = '';

    public $enrollment_status = 'Activo';

    public $currentEnrollment = null;

    public $enrollmentHistory = [];

    protected $queryString = [
        'cant' => ['except' => '10'],
        'sort' => ['except' => 'surname'],
        'direction' => ['except' => 'asc'],
        'search' => ['except' => ''],
    ];

    public function loadStudents()
    {
        $this->readyToLoad = true;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCant()
    {
        $this->resetPage();
    }

    public function order($sort)
    {
        if ($this->sort == $sort) {
            $this->direction = $this->direction == 'desc' ? 'asc' : 'desc';
        } else {
            $this->sort = $sort;
            $this->direction = 'asc';
        }
    }

    public function resetFields()
    {
        $this->userForm->resetForm();
        $this->studentForm->resetForm();
        $this->medicalForm->resetForm();
        $this->activeTab = 'general';
        $this->enrollment_classroom_id = '';
        $this->enrollment_status = 'Activo';
        $this->currentEnrollment = null;
        $this->enrollmentHistory = [];
        $this->resetValidation();
    }

    public function edit($id)
    {
        $this->resetFields();
        $user = User::with(['student.enrollments.classroom.level', 'student.enrollments.classroom.grade', 'student.enrollments.classroom.section', 'medicalRecord'])->findOrFail($id);

        $this->userForm->setUser($user);
        $this->studentForm->setStudent($user->student);
        $this->medicalForm->setMedicalRecord($user->medicalRecord);

        if ($user->student) {
            $currentYear = date('Y');

            $this->currentEnrollment = $user->student->enrollments
                ->filter(fn ($e) => $e->classroom->year == $currentYear)
                ->first();

            if ($this->currentEnrollment) {
                $this->enrollment_classroom_id = $this->currentEnrollment->classroom_id;
                $this->enrollment_status = $this->currentEnrollment->status;
            }

            $this->enrollmentHistory = $user->student->enrollments
                ->filter(fn ($e) => $e->classroom->year != $currentYear)
                ->sortByDesc(fn ($e) => $e->classroom->year)
                ->values();
        }
    }

    public function update()
    {
        $user = $this->userForm->update();
        $this->studentForm->save($user->id);
        $this->medicalForm->save($user->id);

        $this->resetFields();

        $this->dispatch('closeModalMessaje', [
            'title' => '¡Éxito!',
            'message' => 'Información del estudiante actualizada.',
            'type' => 'success',
            'modalId' => 'StudentModal',
        ]);
    }

    // ==========================================
    // PANEL FAMILIAR (ENCARGADOS)
    // ==========================================

    private function refreshManagingStudent($id)
    {
        $user = User::with('student.guardians')->findOrFail($id);

        // Ordenamos la colección en memoria: 1. Papá, 2. Mamá, 3. Encargado
        if ($user->student && $user->student->guardians) {
            $sortedGuardians = $user->student->guardians->sortBy(function ($guardian) {
                $order = ['Papá' => 1, 'Mamá' => 2, 'Encargado' => 3];

                return $order[$guardian->pivot->relationship_type] ?? 4;
            })->values();

            $user->student->setRelation('guardians', $sortedGuardians);
        }

        $this->managingStudent = $user;
    }

    public function getAvailableRelationships()
    {
        $all = ['Papá', 'Mamá', 'Encargado'];

        if (! $this->managingStudent || ! $this->managingStudent->student) {
            return $all;
        }

        // Obtenemos los parentescos que ya están asignados a este estudiante
        $existing = $this->managingStudent->student->guardians->pluck('pivot.relationship_type')->toArray();

        // Si estamos editando a un familiar, volvemos a habilitar su propio parentesco en el select
        if ($this->showGuardianForm && $this->guardianForm->guardian && $this->relationship_type) {
            $existing = array_diff($existing, [$this->relationship_type]);
        }

        // Retornamos solo las opciones que NO están en uso
        return array_values(array_diff($all, $existing));
    }

    public function manageGuardians($id)
    {
        $this->guardianForm->resetForm();
        $this->relationship_type = '';
        $this->showGuardianForm = false;
        $this->resetValidation();

        $this->refreshManagingStudent($id);
    }

    public function createGuardian()
    {
        $this->guardianForm->resetForm();
        $this->relationship_type = '';
        $this->showGuardianForm = true;
        $this->resetValidation();
    }

    public function editGuardian($guardianId)
    {
        $this->resetValidation();
        $guardian = Guardian::findOrFail($guardianId);
        $this->guardianForm->setGuardian($guardian);

        // Recuperamos el parentesco exacto de la tabla pivote
        $pivotGuardian = $this->managingStudent->student->guardians->where('id', $guardianId)->first();
        if ($pivotGuardian) {
            $this->relationship_type = $pivotGuardian->pivot->relationship_type;
        }

        $this->showGuardianForm = true;
    }

    public function cancelGuardianForm()
    {
        $this->guardianForm->resetForm();
        $this->relationship_type = '';
        $this->showGuardianForm = false;
        $this->resetValidation();
    }

    public function saveGuardian()
    {
        $this->validate([
            'relationship_type' => 'required|string|max:255',
        ], [
            'relationship_type.required' => 'Debe especificar el parentesco (Papá, Mamá, Encargado).',
        ]);

        if ($this->guardianForm->guardian) {
            // Actualizar datos del encargado existente
            $guardian = $this->guardianForm->update();

            // Actualizar parentesco en la tabla pivote
            $this->managingStudent->student->guardians()->updateExistingPivot($guardian->id, [
                'relationship_type' => $this->relationship_type,
            ]);
            $mensaje = 'Datos del familiar actualizados correctamente.';
        } else {
            // Crear nuevo encargado
            $guardian = $this->guardianForm->store();

            // Vincular al estudiante
            $this->managingStudent->student->guardians()->attach($guardian->id, [
                'relationship_type' => $this->relationship_type,
            ]);
            $mensaje = 'Nuevo familiar registrado y asignado.';
        }

        // Recargamos el listado familiar y volvemos a la vista de tabla
        $this->refreshManagingStudent($this->managingStudent->id);
        $this->showGuardianForm = false;

        $this->dispatch('toastMessage', [
            'type' => 'success',
            'message' => $mensaje,
        ]);
    }

    public function detachGuardian($guardianId)
    {
        // Validamos por seguridad en el backend que no eliminen al Encargado
        $guardian = $this->managingStudent->student->guardians->where('id', $guardianId)->first();

        if ($guardian && $guardian->pivot->relationship_type === 'Encargado') {
            $this->dispatch('toastMessage', [
                'type' => 'error',
                'message' => 'El Encargado principal no puede ser retirado, solo modificado.',
            ]);

            return;
        }

        $this->managingStudent->student->guardians()->detach($guardianId);
        $this->refreshManagingStudent($this->managingStudent->id);

        $this->dispatch('toastMessage', [
            'type' => 'info',
            'message' => 'Familiar retirado del perfil del estudiante.',
        ]);
    }

    public function saveEnrollment()
    {
        $this->validate([
            'enrollment_classroom_id' => 'required|exists:classrooms,id',
            'enrollment_status' => 'required|in:Activo,Retirado',
        ], [
            'enrollment_classroom_id.required' => 'Debe seleccionar un aula.',
            'enrollment_classroom_id.exists' => 'El aula seleccionada no es válida.',
            'enrollment_status.required' => 'El estado es obligatorio.',
        ]);

        $student = $this->userForm->user->student;
        $currentYear = date('Y');
        $classroom = Classroom::findOrFail($this->enrollment_classroom_id);

        if ($classroom->year != $currentYear) {
            $this->addError('enrollment_classroom_id', 'Solo puede asignar aulas del año actual.');

            return;
        }

        $userLevelIds = Auth::user()->levels()->pluck('levels.id');
        if (! $userLevelIds->contains($classroom->level_id)) {
            $this->addError('enrollment_classroom_id', 'No tiene permiso para asignar aulas de ese nivel.');

            return;
        }

        // Verificar si es un cambio de aula (ignora si solo está cambiando el estado Activo/Retirado)
        if ($this->currentEnrollment && $this->currentEnrollment->classroom_id != $this->enrollment_classroom_id) {

            // Buscar si el estudiante tiene registros de notas (totales) en el aula anterior
            $hasGrades = GradeBookTotal::where('student_id', $student->id)
                ->whereHas('gradeBook.assignment', function ($q) {
                    $q->where('classroom_id', $this->currentEnrollment->classroom_id);
                })->exists();

            if ($hasGrades) {
                // Lanzamos la alerta a la vista para pedir confirmación
                $this->dispatch('confirmClassroomChange', [
                    'title' => '¡Atención! Cambio de Aula',
                    'text' => 'El estudiante ya está asignado a cuadros de notas en su aula actual. Si lo cambia de aula, se eliminarán permanentemente todas sus notas registradas en el aula anterior y se reiniciarán en 0 para el nuevo aula. ¿Desea proceder?',
                ]);

                return; // Detenemos la ejecución hasta que el usuario confirme en el modal
            }
        }

        // Si no hay cambio de aula o no hay cuadros de notas en riesgo, guardamos directamente
        $this->confirmSaveEnrollment();
    }

    #[On('triggerConfirmSaveEnrollment')]
    public function confirmSaveEnrollment()
    {
        $student = $this->userForm->user->student;
        $currentYear = date('Y');

        $oldClassroomId = $this->currentEnrollment ? $this->currentEnrollment->classroom_id : null;
        $newClassroom = Classroom::with(['level', 'grade', 'section'])->find($this->enrollment_classroom_id);

        $notasBorradas = false;
        $oldClassroomName = '';

        DB::transaction(function () use ($student, $oldClassroomId, &$notasBorradas, &$oldClassroomName) {
            // 1. Limpieza e inicialización si hubo cambio de aula
            if ($oldClassroomId && $oldClassroomId != $this->enrollment_classroom_id) {

                $oldClassroomModel = Classroom::with(['level', 'grade', 'section'])->find($oldClassroomId);
                if ($oldClassroomModel) {
                    $oldClassroomName = $oldClassroomModel->grade->grade_name.' '.$oldClassroomModel->section->section_name.' '.$oldClassroomModel->year;
                }

                // Verificar si realmente había notas antes de borrar (para el log)
                $notasBorradas = GradeBookScore::where('student_id', $student->id)
                    ->whereHas('activity.gradeBook.assignment', function ($q) use ($oldClassroomId) {
                        $q->where('classroom_id', $oldClassroomId);
                    })->exists();

                // Eliminar totales (GradeBookTotal) del aula anterior
                GradeBookTotal::where('student_id', $student->id)
                    ->whereHas('gradeBook.assignment', function ($q) use ($oldClassroomId) {
                        $q->where('classroom_id', $oldClassroomId);
                    })->delete();

                // Eliminar detalles de notas (GradeBookScore) del aula anterior
                GradeBookScore::where('student_id', $student->id)
                    ->whereHas('activity.gradeBook.assignment', function ($q) use ($oldClassroomId) {
                        $q->where('classroom_id', $oldClassroomId);
                    })->delete();

                // Inicializar en 0 los cuadros para el nuevo aula
                $this->createEmptyScoresAndTotals($student->id, $this->enrollment_classroom_id);
            }

            // 2. Actualizar o crear inscripción
            if ($this->currentEnrollment) {
                $this->currentEnrollment->update([
                    'classroom_id' => $this->enrollment_classroom_id,
                    'status' => $this->enrollment_status,
                ]);
            } else {
                StudentEnrollment::create([
                    'student_id' => $student->id,
                    'classroom_id' => $this->enrollment_classroom_id,
                    'status' => $this->enrollment_status,
                ]);
            }
        });

        // 3. Registro en Auditoría
        $newClassroomName = $newClassroom->grade->grade_name.' '.$newClassroom->section->section_name.' '.$newClassroom->year;

        $logDescription = $oldClassroomId && $oldClassroomId != $this->enrollment_classroom_id
            ? "El estudiante {$this->userForm->user->name} fue trasladado del aula: {$oldClassroomName} a {$newClassroomName}. ".($notasBorradas ? 'Se eliminaron sus notas del aula anterior.' : 'No tenía notas registradas en el aula anterior.')
            : "Inscripción actualizada para {$this->userForm->user->name} en {$newClassroomName}. Estado: {$this->enrollment_status}.";

        AuditLog::create([
            'user_id' => Auth::id() ?? 1, // El ID del administrador que hizo el cambio
            'event' => 'enrolled',
            'module' => 'Inscripciones',
            'description' => $logDescription,
            'auditable_type' => StudentEnrollment::class,
            'auditable_id' => $this->currentEnrollment ? $this->currentEnrollment->id : null,
            'old_values' => $oldClassroomId && $oldClassroomId != $this->enrollment_classroom_id ? ['classroom' => $oldClassroomName] : null,
            'new_values' => [
                'status' => $this->enrollment_status,
                'classroom' => $newClassroomName,
            ],
            'ip_address' => request()->ip(),
        ]);

        // Recargar datos para la vista
        $user = User::with(['student.enrollments.classroom.level', 'student.enrollments.classroom.grade', 'student.enrollments.classroom.section'])->find($this->userForm->user->id);

        $this->currentEnrollment = $user->student->enrollments
            ->filter(fn ($e) => $e->classroom->year == $currentYear)
            ->first();

        $this->enrollment_classroom_id = $this->currentEnrollment->classroom_id;
        $this->enrollment_status = $this->currentEnrollment->status;

        $this->enrollmentHistory = $user->student->enrollments
            ->filter(fn ($e) => $e->classroom->year != $currentYear)
            ->sortByDesc(fn ($e) => $e->classroom->year)
            ->values();

        $this->dispatch('toastMessage', [
            'type' => 'success',
            'message' => 'Inscripción guardada y actualizada correctamente.',
        ]);
    }

    protected function createEmptyScoresAndTotals(int $studentId, int $classroomId): void
    {
        $gradeBooks = GradeBook::whereHas(
            'assignment',
            fn ($q) => $q->where('classroom_id', $classroomId)
        )->with('activities')->get();

        foreach ($gradeBooks as $gradeBook) {
            foreach ($gradeBook->activities as $activity) {
                GradeBookScore::firstOrCreate(
                    ['grade_book_activity_id' => $activity->id, 'student_id' => $studentId],
                    ['score' => 0, 'improvement_score' => 0]
                );
            }
            GradeBookTotal::firstOrCreate(
                ['grade_book_id' => $gradeBook->id, 'student_id' => $studentId],
                ['normal_points' => 0, 'extra_points' => 0, 'total_points' => 0]
            );
        }
    }

    public function render()
    {
        if ($this->readyToLoad) {
            $students = User::role('Estudiante')
                ->where(function ($query) {
                    $query->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('cui', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                })
                ->orderBy($this->sort, $this->direction)
                ->paginate($this->cant);
        } else {
            $students = [];
        }

        $userLevelIds = Auth::user()->levels()->pluck('levels.id');

        $currentYearClassrooms = Classroom::with(['level', 'grade', 'section'])
            ->where('year', date('Y'))
            ->whereIn('level_id', $userLevelIds)
            ->orderBy('level_id')
            ->get();

        return view('livewire.students.student-list', compact('students', 'currentYearClassrooms'));
    }
}
