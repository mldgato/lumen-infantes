<?php

namespace App\Livewire\Admin\Students;

use App\Models\Classroom;
use App\Models\Grade;
use App\Models\GradeBook;
use App\Models\GradeBookScore;
use App\Models\GradeBookTotal;
use App\Models\Guardian;
use App\Models\Level;
use App\Models\MedicalRecord;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;
use App\Services\AuditService;

class EnrollmentList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public bool   $readyToLoad   = false;
    public string $filterYear    = '';
    public string $filterLevel   = '';
    public string $filterGrade   = '';
    public string $filterSection = '';

    public string $modalMode  = 'new';
    public string $activeTab  = 'general';

    // Search existing
    public string $searchStudent       = '';
    public array  $searchResults       = [];
    public ?int   $selectedStudentId   = null;
    public string $selectedStudentName = '';

    // ==========================================
    // DATOS GENERALES
    // ==========================================
    public string $cui             = '';
    public string $first_name      = '';
    public string $middle_name     = '';
    public string $surname         = '';
    public string $second_surname  = '';
    public string $married_surname = '';
    public string $birthdate       = '';
    public string $gender          = '';
    public string $civil_status    = '';
    public string $email           = '';
    public string $personal_email  = '';
    public string $password        = '';
    public string $cellphone       = '';
    public string $address         = '';
    public string $carne           = '';
    public string $personal_code   = '';
    public bool   $is_own_guardian = false;

    // ==========================================
    // FICHA MÉDICA
    // ==========================================
    public string $blood_type              = '';
    public string $weight                  = '';
    public string $height                  = '';
    public bool   $takes_medication        = false;
    public string $medication_description  = '';
    public bool   $has_disease             = false;
    public string $disease_description     = '';
    public bool   $has_allergies           = false;
    public string $allergies_description   = '';
    public bool   $had_surgery             = false;
    public string $surgery_description     = '';

    // ==========================================
    // GUARDIANES (padre, madre, encargado)
    // ==========================================
    public array $guardians = [
        'padre'     => ['enabled' => false, 'data' => []],
        'madre'     => ['enabled' => false, 'data' => []],
        'encargado' => ['enabled' => false, 'data' => []],
    ];

    protected $queryString = [
        'filterYear'    => ['except' => ''],
        'filterLevel'   => ['except' => ''],
        'filterGrade'   => ['except' => ''],
        'filterSection' => ['except' => ''],
    ];

    private function emptyGuardian(): array
    {
        return [
            'first_name'        => '',
            'last_name'         => '',
            'birthplace'        => '',
            'birthdate'         => '',
            'nationality'       => '',
            'cui'               => '',
            'cui_extended_in'   => '',
            'profession'        => '',
            'residence_address' => '',
            'phone'             => '',
            'email'             => '',
            'company_name'      => '',
            'company_address'   => '',
            'company_phone'     => '',
        ];
    }

    public function mount(): void
    {
        $this->resetGuardians();
    }

    private function resetGuardians(): void
    {
        $this->guardians = [
            'padre'     => ['enabled' => false, 'data' => $this->emptyGuardian()],
            'madre'     => ['enabled' => false, 'data' => $this->emptyGuardian()],
            'encargado' => ['enabled' => false, 'data' => $this->emptyGuardian()],
        ];
    }

    public function loadData(): void
    {
        $this->readyToLoad = true;
    }

    public function updatedFilterYear(): void
    {
        $this->filterLevel = $this->filterGrade = $this->filterSection = '';
        $this->resetPage();
    }
    public function updatedFilterLevel(): void
    {
        $this->filterGrade = $this->filterSection = '';
        $this->resetPage();
    }
    public function updatedFilterGrade(): void
    {
        $this->filterSection = '';
        $this->resetPage();
    }
    public function updatedFilterSection(): void
    {
        $this->resetPage();
    }

    public function updatedSearchStudent(): void
    {
        if (strlen($this->searchStudent) < 2) {
            $this->searchResults = [];
            return;
        }

        $this->searchResults = User::whereHas('student')
            ->where(
                fn($q) =>
                $q->where('name', 'like', '%' . $this->searchStudent . '%')
                    ->orWhere('cui', 'like', '%' . $this->searchStudent . '%')
                    ->orWhereHas(
                        'student',
                        fn($q) =>
                        $q->where('carne', 'like', '%' . $this->searchStudent . '%')
                            ->orWhere('personal_code', 'like', '%' . $this->searchStudent . '%')
                    )
            )
            ->limit(8)
            ->get()
            ->map(fn($u) => [
                'id'           => $u->student->id,
                'name'         => $u->name,
                'cui'          => $u->cui,
                'carne'        => $u->student->carne ?? '—',
                'personal_code' => $u->student->personal_code ?? '—',
            ])
            ->toArray();
    }

    public function selectStudent(int $studentId, string $name): void
    {
        $this->selectedStudentId   = $studentId;
        $this->selectedStudentName = $name;
        $this->searchResults       = [];
        $this->searchStudent       = $name;
    }

    public function openModal(string $mode): void
    {
        $this->resetModal();
        $this->modalMode = $mode;
        $this->dispatch('openEnrollmentModal');
    }

    public function resetModal(): void
    {
        $this->modalMode           = 'new';
        $this->activeTab           = 'general';
        $this->searchStudent       = '';
        $this->searchResults       = [];
        $this->selectedStudentId   = null;
        $this->selectedStudentName = '';

        // General
        $this->cui = $this->first_name = $this->middle_name = $this->surname = '';
        $this->second_surname = $this->married_surname = $this->birthdate = '';
        $this->gender = $this->civil_status = $this->email = $this->personal_email = '';
        $this->password = $this->cellphone = $this->address = '';
        $this->carne = $this->personal_code = '';
        $this->is_own_guardian = false;

        // Médica
        $this->blood_type = $this->weight = $this->height = '';
        $this->takes_medication = $this->has_disease = $this->has_allergies = $this->had_surgery = false;
        $this->medication_description = $this->disease_description = '';
        $this->allergies_description  = $this->surgery_description  = '';

        $this->resetGuardians();
        $this->resetValidation();
    }

    public function enrollExisting(): void
    {
        $this->validate([
            'selectedStudentId' => 'required|exists:students,id',
            'filterSection'     => 'required',
        ], [
            'selectedStudentId.required' => 'Debe seleccionar un estudiante.',
            'filterSection.required'     => 'Seleccione un aula primero.',
        ]);

        $classroom = $this->getSelectedClassroom();
        if (! $classroom) return;

        $student = Student::findOrFail($this->selectedStudentId);

        $alreadyEnrolled = StudentEnrollment::where('student_id', $student->id)
            ->whereHas('classroom', fn($q) => $q->where('year', $this->filterYear))
            ->exists();

        if ($alreadyEnrolled) {
            $this->addError('selectedStudentId', 'El estudiante ya está inscrito en un aula para el año ' . $this->filterYear . '.');
            return;
        }

        DB::transaction(function () use ($student, $classroom) {
            $enrollment = StudentEnrollment::create([
                'student_id'   => $student->id,
                'classroom_id' => $classroom->id,
                'status'       => 'Activo',
            ]);

            AuditService::enrollmentCreated(
                $enrollment->load('student.user', 'classroom.grade', 'classroom.section')
            );

            $this->createEmptyScoresAndTotals($student->id, $classroom->id);
        });

        $this->resetModal();
        $this->dispatch('closeModalMessaje', [
            'title'   => '¡Inscrito!',
            'message' => 'El estudiante fue inscrito exitosamente.',
            'type'    => 'success',
            'modalId' => 'EnrollmentModal',
        ]);
    }

    public function enrollNew(): void
    {
        $this->validate([
            'cui'           => 'required|string|max:20|unique:users,cui',
            'first_name'    => 'required|string|max:100',
            'surname'       => 'required|string|max:100',
            'birthdate'     => 'required|date',
            'gender'        => 'required|in:Masculino,Femenino',
            'email'         => 'required|email|unique:users,email',
            'password'      => 'required|string|min:6',
            'filterSection' => 'required',
        ], [
            'cui.required'           => 'El CUI es obligatorio.',
            'cui.unique'             => 'Ya existe un usuario con ese CUI.',
            'first_name.required'    => 'El primer nombre es obligatorio.',
            'surname.required'       => 'El primer apellido es obligatorio.',
            'birthdate.required'     => 'La fecha de nacimiento es obligatoria.',
            'gender.required'        => 'El género es obligatorio.',
            'email.required'         => 'El correo es obligatorio.',
            'email.unique'           => 'El correo ya está en uso.',
            'password.required'      => 'La contraseña es obligatoria.',
            'filterSection.required' => 'Seleccione un aula primero.',
        ]);

        $classroom = $this->getSelectedClassroom();
        if (! $classroom) return;

        DB::transaction(function () use ($classroom) {
            $user = User::create([
                'cui'            => $this->cui,
                'first_name'     => $this->first_name,
                'middle_name'    => $this->middle_name     ?: null,
                'surname'        => $this->surname,
                'second_surname' => $this->second_surname  ?: null,
                'married_surname' => $this->married_surname ?: null,
                'birthdate'      => $this->birthdate,
                'gender'         => $this->gender,
                'civil_status'   => $this->civil_status    ?: null,
                'email'          => $this->email,
                'personal_email' => $this->personal_email  ?: null,
                'password'       => Hash::make($this->password),
                'cellphone'      => $this->cellphone        ?: null,
                'address'        => $this->address          ?: null,
                'is_active'      => true,
            ]);

            $user->assignRole('Estudiante');

            $student = Student::create([
                'user_id'         => $user->id,
                'carne'           => $this->carne         ?: null,
                'personal_code'   => $this->personal_code ?: null,
                'is_own_guardian' => $this->is_own_guardian,
            ]);

            // Ficha médica
            MedicalRecord::create([
                'user_id'                => $user->id,
                'blood_type'             => $this->blood_type            ?: null,
                'weight'                 => $this->weight                ?: null,
                'height'                 => $this->height                ?: null,
                'takes_medication'       => $this->takes_medication,
                'medication_description' => $this->takes_medication ? ($this->medication_description ?: null) : null,
                'has_disease'            => $this->has_disease,
                'disease_description'    => $this->has_disease    ? ($this->disease_description    ?: null) : null,
                'has_allergies'          => $this->has_allergies,
                'allergies_description'  => $this->has_allergies  ? ($this->allergies_description  ?: null) : null,
                'had_surgery'            => $this->had_surgery,
                'surgery_description'    => $this->had_surgery    ? ($this->surgery_description    ?: null) : null,
            ]);

            // Guardianes
            $relationshipMap = [
                'padre'     => 'Padre',
                'madre'     => 'Madre',
                'encargado' => 'Encargado',
            ];

            foreach ($this->guardians as $key => $guardian) {
                if (! $guardian['enabled']) continue;
                $d = $guardian['data'];
                if (empty($d['first_name']) || empty($d['last_name'])) continue;

                $guardianModel = Guardian::create([
                    'first_name'        => $d['first_name'],
                    'last_name'         => $d['last_name'],
                    'birthplace'        => $d['birthplace']        ?: null,
                    'birthdate'         => $d['birthdate']         ?: null,
                    'nationality'       => $d['nationality']       ?: null,
                    'cui'               => $d['cui']               ?: null,
                    'cui_extended_in'   => $d['cui_extended_in']   ?: null,
                    'profession'        => $d['profession']        ?: null,
                    'residence_address' => $d['residence_address'] ?: null,
                    'phone'             => $d['phone']             ?: null,
                    'email'             => $d['email']             ?: null,
                    'company_name'      => $d['company_name']      ?: null,
                    'company_address'   => $d['company_address']   ?: null,
                    'company_phone'     => $d['company_phone']     ?: null,
                ]);

                $student->guardians()->attach($guardianModel->id, [
                    'relationship_type' => $relationshipMap[$key],
                ]);
            }

            $enrollment = StudentEnrollment::create([
                'student_id'   => $student->id,
                'classroom_id' => $classroom->id,
                'status'       => 'Activo',
            ]);

            AuditService::enrollmentCreated(
                $enrollment->load('student.user', 'classroom.grade', 'classroom.section')
            );

            $this->createEmptyScoresAndTotals($student->id, $classroom->id);
        });

        $this->resetModal();
        $this->dispatch('closeModalMessaje', [
            'title'   => '¡Inscrito!',
            'message' => 'El estudiante fue creado e inscrito exitosamente.',
            'type'    => 'success',
            'modalId' => 'EnrollmentModal',
        ]);
    }

    public function changeStatus(int $enrollmentId, string $status): void
    {
        $enrollment = StudentEnrollment::findOrFail($enrollmentId);
        $oldStatus  = $enrollment->status;
        $enrollment->update(['status' => $status]);

        AuditService::enrollmentStatusChanged(
            $enrollment->load('student.user'),
            $oldStatus,
            $status
        );

        $this->dispatch('toastMessage', ['type' => 'success', 'message' => 'Estado actualizado a ' . $status . '.']);
    }

    protected function createEmptyScoresAndTotals(int $studentId, int $classroomId): void
    {
        $gradeBooks = GradeBook::whereHas(
            'assignment',
            fn($q) =>
            $q->where('classroom_id', $classroomId)
        )->with('activities')->get();

        foreach ($gradeBooks as $gradeBook) {
            foreach ($gradeBook->activities as $activity) {
                GradeBookScore::firstOrCreate(
                    ['grade_book_activity_id' => $activity->id, 'student_id' => $studentId],
                    ['score' => null, 'improvement_score' => null]
                );
            }
            GradeBookTotal::firstOrCreate(
                ['grade_book_id' => $gradeBook->id, 'student_id' => $studentId],
                ['normal_points' => 0, 'extra_points' => 0, 'total_points' => 0]
            );
        }
    }

    protected function getSelectedClassroom(): ?Classroom
    {
        if (! $this->filterYear || ! $this->filterLevel || ! $this->filterGrade || ! $this->filterSection) return null;
        return Classroom::where('year', $this->filterYear)
            ->where('level_id', $this->filterLevel)
            ->where('grade_id', $this->filterGrade)
            ->where('section_id', $this->filterSection)
            ->first();
    }

    public function render()
    {
        $years    = Classroom::select('year')->distinct()->orderByDesc('year')->pluck('year');
        $levels   = Level::orderBy('level_name')->get();
        $grades   = $this->filterLevel
            ? Grade::whereHas('classrooms', fn($q) => $q->where('level_id', $this->filterLevel)->when($this->filterYear, fn($q) => $q->where('year', $this->filterYear)))->orderBy('ordering')->get()
            : collect();
        $sections = $this->filterGrade
            ? Section::whereHas('classrooms', fn($q) => $q->where('grade_id', $this->filterGrade)->where('level_id', $this->filterLevel)->when($this->filterYear, fn($q) => $q->where('year', $this->filterYear)))->orderBy('section_name')->get()
            : collect();

        $classroom = $this->getSelectedClassroom();
        $enrollments = collect();
        $totalActive = $totalInactive = $totalRetired = 0;

        if ($classroom && $this->readyToLoad) {
            $q = StudentEnrollment::with(['student.user'])
                ->where('classroom_id', $classroom->id)
                ->join('students', 'student_enrollments.student_id', '=', 'students.id')
                ->join('users', 'students.user_id', '=', 'users.id')
                ->orderBy('users.surname')->orderBy('users.second_surname')->orderBy('users.first_name')
                ->select('student_enrollments.*');

            $totalActive   = (clone $q)->where('status', 'Activo')->count();
            $totalInactive = (clone $q)->where('status', 'Inactivo')->count();
            $totalRetired  = (clone $q)->where('status', 'Retirado')->count();
            $enrollments   = $q->paginate(25);
        }

        return view('livewire.admin.students.enrollment-list', compact(
            'years',
            'levels',
            'grades',
            'sections',
            'classroom',
            'enrollments',
            'totalActive',
            'totalInactive',
            'totalRetired'
        ));
    }
}
