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
use Illuminate\Support\Facades\Log;

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
    public string $encargado_role = 'otro';

    // ==========================================
    // Requerir correo institucional
    // ==========================================
    public bool $requireInstitutionalEmail = true;

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
        $this->requireInstitutionalEmail = (bool) config('lumen.require_institutional_email', true);
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

    public function updatedPersonalEmail(string $value): void
    {
        if (! $this->requireInstitutionalEmail) {
            $this->email = $value;
        }
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
        $this->encargado_role  = 'otro';

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
        $rules = [
            'cui'           => 'required|string|max:20|unique:users,cui',
            'first_name'    => 'required|string|max:100',
            'surname'       => 'required|string|max:100',
            'birthdate'     => 'required|date',
            'gender'        => 'required|in:Masculino,Femenino',
            'filterSection' => 'required',
            'weight'        => 'nullable|numeric',
            'height'        => 'nullable|numeric',
            'personal_email' => 'nullable|email',
        ];

        $messages = [
            'cui.required'           => 'El CUI es obligatorio.',
            'cui.unique'             => 'Ya existe un usuario con ese CUI.',
            'first_name.required'    => 'El primer nombre es obligatorio.',
            'surname.required'       => 'El primer apellido es obligatorio.',
            'birthdate.required'     => 'La fecha de nacimiento es obligatoria.',
            'gender.required'        => 'El género es obligatorio.',
            'filterSection.required' => 'Seleccione un aula primero.',
            'weight.numeric'         => 'El peso debe ser un número válido.',
            'height.numeric'         => 'La estatura debe ser un número válido.',
            'personal_email.email'   => 'El correo personal no tiene un formato válido.',
        ];

        if ($this->requireInstitutionalEmail) {
            $rules['email']    = 'required|email|unique:users,email';
            $rules['password'] = 'required|string|min:6';

            $messages['email.required']    = 'El correo institucional es obligatorio.';
            $messages['email.unique']      = 'El correo institucional ya está en uso.';
            $messages['password.required'] = 'La contraseña es obligatoria.';
        }

        // Solo validamos los guardianes que el usuario habilitó manualmente.
        foreach (['padre', 'madre', 'encargado'] as $key) {
            if (! ($this->guardians[$key]['enabled'] ?? false)) {
                continue;
            }

            $rules["guardians.$key.data.first_name"]        = 'required|string';
            $rules["guardians.$key.data.last_name"]         = 'required|string';
            $rules["guardians.$key.data.birthdate"]         = 'required|date';
            $rules["guardians.$key.data.nationality"]       = 'required|string';
            $rules["guardians.$key.data.cui"]               = 'required|string';
            $rules["guardians.$key.data.cui_extended_in"]   = 'required|string';
            $rules["guardians.$key.data.profession"]        = 'required|string';
            $rules["guardians.$key.data.residence_address"] = 'required|string';
            $rules["guardians.$key.data.phone"]             = 'required|string';

            $label = ucfirst($key);
            $messages["guardians.$key.data.first_name.required"]        = "El nombre del $label es obligatorio.";
            $messages["guardians.$key.data.last_name.required"]         = "El apellido del $label es obligatorio.";
            $messages["guardians.$key.data.birthdate.required"]         = "La fecha de nacimiento del $label es obligatoria.";
            $messages["guardians.$key.data.nationality.required"]       = "La nacionalidad del $label es obligatoria.";
            $messages["guardians.$key.data.cui.required"]               = "El CUI del $label es obligatorio.";
            $messages["guardians.$key.data.cui_extended_in.required"]   = "El lugar de extensión del CUI del $label es obligatorio.";
            $messages["guardians.$key.data.profession.required"]        = "La profesión del $label es obligatoria.";
            $messages["guardians.$key.data.residence_address.required"] = "La dirección del $label es obligatoria.";
            $messages["guardians.$key.data.phone.required"]             = "El teléfono del $label es obligatorio.";
        }

        try {
            $this->validate($rules, $messages);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->jumpToTabWithError(array_key_first($e->validator->errors()->toArray()));
            throw $e;
        }

        $classroom = $this->getSelectedClassroom();
        if (! $classroom) {
            $this->addError('filterSection', 'No se pudo resolver el aula seleccionada.');
            $this->activeTab = 'general';
            return;
        }

        // Resolver correo de login:
        // 1) Si requiere institucional, usar el que el usuario escribió.
        // 2) Si no, usar el personal si lo escribió.
        // 3) Si no hay ninguno, generar uno automático basado en el CUI.
        $resolvedEmail = $this->requireInstitutionalEmail
            ? $this->email
            : ($this->personal_email ?: $this->generateAutoEmail());

        $resolvedPassword = $this->requireInstitutionalEmail ? $this->password : 'password';

        try {
            DB::transaction(function () use ($classroom, $resolvedEmail, $resolvedPassword) {
                $user = User::create([
                    'cui'             => $this->cui,
                    'first_name'      => $this->first_name,
                    'middle_name'     => $this->middle_name      ?: null,
                    'surname'         => $this->surname,
                    'second_surname'  => $this->second_surname   ?: null,
                    'married_surname' => $this->married_surname  ?: null,
                    'birthdate'       => $this->birthdate,
                    'gender'          => $this->gender,
                    'civil_status'    => $this->civil_status     ?: null,
                    'email'           => $resolvedEmail,
                    'personal_email'  => $this->personal_email   ?: null,
                    'password'        => Hash::make($resolvedPassword),
                    'cellphone'       => $this->cellphone        ?: null,
                    'address'         => $this->address          ?: null,
                    'is_active'       => true,
                ]);

                $user->assignRole('Estudiante');

                $student = Student::create([
                    'user_id'         => $user->id,
                    'carne'           => $this->carne          ?: null,
                    'personal_code'   => $this->personal_code  ?: null,
                    'is_own_guardian' => $this->is_own_guardian,
                ]);

                MedicalRecord::create([
                    'user_id'                => $user->id,
                    'blood_type'             => $this->blood_type ?: null,
                    'weight'                 => is_numeric($this->weight) ? $this->weight : null,
                    'height'                 => is_numeric($this->height) ? $this->height : null,
                    'takes_medication'       => $this->takes_medication,
                    'medication_description' => $this->takes_medication ? ($this->medication_description ?: null) : null,
                    'has_disease'            => $this->has_disease,
                    'disease_description'    => $this->has_disease    ? ($this->disease_description    ?: null) : null,
                    'has_allergies'          => $this->has_allergies,
                    'allergies_description'  => $this->has_allergies  ? ($this->allergies_description  ?: null) : null,
                    'had_surgery'            => $this->had_surgery,
                    'surgery_description'    => $this->had_surgery    ? ($this->surgery_description    ?: null) : null,
                ]);

                $relationshipMap = [
                    'padre'     => 'Papá',
                    'madre'     => 'Mamá',
                    'encargado' => 'Encargado',
                ];

                foreach ($this->guardians as $key => $guardian) {
                    if (! ($guardian['enabled'] ?? false)) {
                        continue;
                    }

                    $d = $guardian['data'];

                    $guardianModel = ! empty($d['cui'])
                        ? Guardian::where('cui', $d['cui'])->first()
                        : null;

                    if (! $guardianModel) {
                        $guardianModel = Guardian::create([
                            'first_name'        => $d['first_name'],
                            'last_name'         => $d['last_name'],
                            'birthplace'        => $d['birthplace']        ?: null,
                            'birthdate'         => $d['birthdate'],
                            'nationality'       => $d['nationality'],
                            'cui'               => $d['cui'],
                            'cui_extended_in'   => $d['cui_extended_in'],
                            'profession'        => $d['profession'],
                            'residence_address' => $d['residence_address'],
                            'phone'             => $d['phone'],
                            'email'             => $d['email']             ?: null,
                            'company_name'      => $d['company_name']      ?: null,
                            'company_address'   => $d['company_address']   ?: null,
                            'company_phone'     => $d['company_phone']     ?: null,
                        ]);
                    }

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
        } catch (\Throwable $e) {
            Log::error('Error al inscribir nuevo estudiante: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            $this->dispatch('toastMessage', [
                'type'    => 'error',
                'message' => 'Ocurrió un error al guardar: ' . $e->getMessage(),
            ]);
            return;
        }

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
                    ['score' => 0, 'improvement_score' => 0]
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

    public function updatedIsOwnGuardian($value): void
    {
        if ($value) {
            $this->encargado_role = 'estudiante';
            $this->fillEncargadoWith('estudiante');
        } else {
            if ($this->encargado_role === 'estudiante') {
                $this->encargado_role = 'otro';
                $this->guardians['encargado']['enabled'] = false;
                $this->resetEncargado();
            }
        }
    }

    public function updatedEncargadoRole($value): void
    {
        if ($value === 'estudiante') {
            $this->is_own_guardian = true;
            $this->fillEncargadoWith('estudiante');
        } else {
            $this->is_own_guardian = false;

            if ($value === 'padre' || $value === 'madre') {
                $this->fillEncargadoWith($value);
            } else {
                $this->resetEncargado();
            }
        }
    }

    private function fillEncargadoWith(string $source): void
    {
        $this->guardians['encargado']['enabled'] = true;

        if ($source === 'estudiante') {
            $this->guardians['encargado']['data'] = array_merge($this->emptyGuardian(), [
                'first_name'        => trim($this->first_name . ' ' . $this->middle_name),
                'last_name'         => trim($this->surname . ' ' . $this->second_surname),
                'cui'               => $this->cui,
                'cui_extended_in'   => 'Guatemala',
                'nationality'       => 'Guatemalteca',
                'profession'        => 'Estudiante',
                'birthdate'         => $this->birthdate,
                'phone'             => $this->cellphone ?: 'N/A',
                'email'             => $this->personal_email,
                'residence_address' => $this->address ?: 'N/A',
            ]);
        } elseif ($source === 'padre' || $source === 'madre') {
            $this->guardians['encargado']['data'] = $this->guardians[$source]['data'];
        }
    }

    private function resetEncargado(): void
    {
        $this->guardians['encargado']['data'] = $this->emptyGuardian();
    }

    /**
     * Cambia automáticamente al tab que contiene el primer error de validación,
     * para que el usuario vea qué campo falló sin tener que buscarlo.
     */
    private function jumpToTabWithError(?string $firstErrorKey): void
    {
        if (! $firstErrorKey) {
            return;
        }

        if (str_starts_with($firstErrorKey, 'guardians.')) {
            $this->activeTab = 'guardians';
            return;
        }

        if (in_array($firstErrorKey, ['blood_type', 'weight', 'height', 'medication_description', 'disease_description', 'allergies_description', 'surgery_description'], true)) {
            $this->activeTab = 'medical';
            return;
        }

        $this->activeTab = 'general';
    }

    /**
     * Genera un correo institucional automático único basado en el CUI,
     * para casos donde no se exige correo institucional ni personal.
     */
    private function generateAutoEmail(): string
    {
        $domain = config('lumen.auto_email_domain', 'cmr.deproweb.net');
        $base   = 'estudiante' . $this->cui;
        $email  = $base . '@' . $domain;

        $i = 1;
        while (User::where('email', $email)->exists()) {
            $email = $base . '.' . $i . '@' . $domain;
            $i++;
        }

        return $email;
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
        $totalActive = $totalRetired = 0; // Se eliminó $totalInactive

        if ($classroom && $this->readyToLoad) {
            $q = StudentEnrollment::with(['student.user'])
                ->where('classroom_id', $classroom->id)
                ->join('students', 'student_enrollments.student_id', '=', 'students.id')
                ->join('users', 'students.user_id', '=', 'users.id')
                ->orderBy('users.surname')->orderBy('users.second_surname')->orderBy('users.first_name')
                ->select('student_enrollments.*');

            $totalActive   = (clone $q)->where('status', 'Activo')->count();
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
            'totalRetired'
        ));
    }
}
