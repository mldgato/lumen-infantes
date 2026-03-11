<?php

namespace App\Livewire\Students;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\Guardian;
use App\Livewire\Forms\UserForm;
use App\Livewire\Forms\StudentForm;
use App\Livewire\Forms\MedicalForm;
use App\Livewire\Forms\GuardianForm;
use App\Models\Classroom;
use App\Models\StudentEnrollment;

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
    public $enrollment_status       = 'Activo';
    public $currentEnrollment       = null;
    public $enrollmentHistory       = [];

    protected $queryString = [
        'cant' => ['except' => '10'],
        'sort' => ['except' => 'surname'],
        'direction' => ['except' => 'asc'],
        'search' => ['except' => '']
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
        $this->activeTab              = 'general';
        $this->enrollment_classroom_id = '';
        $this->enrollment_status      = 'Activo';
        $this->currentEnrollment      = null;
        $this->enrollmentHistory      = [];
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
                ->filter(fn($e) => $e->classroom->year == $currentYear)
                ->first();

            if ($this->currentEnrollment) {
                $this->enrollment_classroom_id = $this->currentEnrollment->classroom_id;
                $this->enrollment_status       = $this->currentEnrollment->status;
            }

            $this->enrollmentHistory = $user->student->enrollments
                ->filter(fn($e) => $e->classroom->year != $currentYear)
                ->sortByDesc(fn($e) => $e->classroom->year)
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
            'modalId' => 'StudentModal'
        ]);
    }

    // ==========================================
    // PANEL FAMILIAR (ENCARGADOS)
    // ==========================================

    public function manageGuardians($id)
    {
        $this->guardianForm->resetForm();
        $this->relationship_type = '';
        $this->showGuardianForm = false;
        $this->resetValidation();

        $this->managingStudent = User::with('student.guardians')->findOrFail($id);
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
            'relationship_type.required' => 'Debe especificar el parentesco (Padre, Madre, etc.).'
        ]);

        if ($this->guardianForm->guardian) {
            // Actualizar datos del encargado existente
            $guardian = $this->guardianForm->update();

            // Actualizar parentesco en la tabla pivote
            $this->managingStudent->student->guardians()->updateExistingPivot($guardian->id, [
                'relationship_type' => $this->relationship_type
            ]);
            $mensaje = 'Datos del encargado actualizados correctamente.';
        } else {
            // Crear nuevo encargado
            $guardian = $this->guardianForm->store();

            // Vincular al estudiante
            $this->managingStudent->student->guardians()->attach($guardian->id, [
                'relationship_type' => $this->relationship_type
            ]);
            $mensaje = 'Nuevo encargado registrado y asignado.';
        }

        // Recargamos el listado familiar y volvemos a la vista de tabla
        $this->managingStudent = User::with('student.guardians')->find($this->managingStudent->id);
        $this->showGuardianForm = false;

        $this->dispatch('toastMessage', [
            'type' => 'success',
            'message' => $mensaje
        ]);
    }

    public function detachGuardian($guardianId)
    {
        $this->managingStudent->student->guardians()->detach($guardianId);
        $this->managingStudent = User::with('student.guardians')->find($this->managingStudent->id);

        $this->dispatch('toastMessage', [
            'type' => 'info',
            'message' => 'Encargado retirado del perfil del estudiante.'
        ]);
    }

    public function saveEnrollment()
    {
        $this->validate([
            'enrollment_classroom_id' => 'required|exists:classrooms,id',
            'enrollment_status'       => 'required|in:Activo,Retirado',
        ], [
            'enrollment_classroom_id.required' => 'Debe seleccionar un aula.',
            'enrollment_classroom_id.exists'   => 'El aula seleccionada no es válida.',
            'enrollment_status.required'       => 'El estado es obligatorio.',
        ]);

        $student = $this->userForm->user->student;
        $currentYear = date('Y');

        $classroom = Classroom::findOrFail($this->enrollment_classroom_id);

        if ($classroom->year != $currentYear) {
            $this->addError('enrollment_classroom_id', 'Solo puede asignar aulas del año actual.');
            return;
        }

        StudentEnrollment::updateOrCreate(
            [
                'student_id'   => $student->id,
                'classroom_id' => $this->enrollment_classroom_id,
            ],
            [
                'status' => $this->enrollment_status,
            ]
        );

        // Recargar
        $user = User::with(['student.enrollments.classroom.level', 'student.enrollments.classroom.grade', 'student.enrollments.classroom.section'])->find($this->userForm->user->id);

        $this->currentEnrollment = $user->student->enrollments
            ->filter(fn($e) => $e->classroom->year == $currentYear)
            ->first();

        $this->enrollment_classroom_id = $this->currentEnrollment->classroom_id;
        $this->enrollment_status       = $this->currentEnrollment->status;

        $this->enrollmentHistory = $user->student->enrollments
            ->filter(fn($e) => $e->classroom->year != $currentYear)
            ->sortByDesc(fn($e) => $e->classroom->year)
            ->values();

        $this->dispatch('toastMessage', [
            'type'    => 'success',
            'message' => 'Inscripción guardada correctamente.',
        ]);
    }

    public function render()
    {
        if ($this->readyToLoad) {
            $students = User::role('Estudiante')
                ->where(function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('cui', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                })
                ->orderBy($this->sort, $this->direction)
                ->paginate($this->cant);
        } else {
            $students = [];
        }

        $currentYearClassrooms = Classroom::with(['level', 'grade', 'section'])
            ->where('year', date('Y'))
            ->get();

        return view('livewire.students.student-list', compact('students', 'currentYearClassrooms'));
    }
}
