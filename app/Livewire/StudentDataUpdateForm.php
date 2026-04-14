<?php

namespace App\Livewire;

use App\Models\Guardian;
use App\Models\MedicalRecord;
use App\Models\Student;
use App\Models\StudentDataUpdate;
use App\Services\AuditService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Request;
use Livewire\Component;

class StudentDataUpdateForm extends Component
{
    // ── Token y sesión ────────────────────────────────────────────────────────
    public string $token = '';

    public int $studentId = 0;

    public string $emailNuevo = '';

    public bool $tokenExpired = false;

    // ── Navegación de tabs ────────────────────────────────────────────────────
    public string $activeTab = 'personal';

    // ── Tab 1 — Datos Personales ──────────────────────────────────────────────
    // Solo lectura (display)
    public string $cui = '';

    public string $firstName = '';

    public string $middleName = '';

    public string $surname = '';

    public string $secondSurname = '';

    // Editable
    public string $marriedSurname = '';

    public string $civilStatus = '';

    public string $birthdate = '';

    public string $gender = '';

    public string $email = '';   // pre-llenado, no editable

    public string $cellphone = '';

    public string $address = '';

    // ── Tab 2 — Ficha Médica ──────────────────────────────────────────────────
    public bool $takesMedication = false;

    public string $medicationDescription = '';

    public bool $hasDisease = false;

    public string $diseaseDescription = '';

    public bool $hasAllergies = false;

    public string $allergiesDescription = '';

    public bool $hadSurgery = false;

    public string $surgeryDescription = '';

    public string $bloodType = '';

    public string $weight = '';

    public string $height = '';

    // ── Tab 3 — Encargados ───────────────────────────────────────────────────
    public string $encargadoRole = 'otro';

    public array $guardians = [
        'padre'     => ['enabled' => false, 'data' => []],
        'madre'     => ['enabled' => false, 'data' => []],
        'encargado' => ['enabled' => false, 'data' => []],
    ];

    public array  $originalGuardianKeys = [];
    public bool   $showDeleteWarning    = false;
    public array  $pendingDeleteLabels  = [];
    public bool   $confirmingDelete     = false;

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

    private function resetGuardians(): void
    {
        $this->guardians = [
            'padre'     => ['enabled' => false, 'data' => $this->emptyGuardian()],
            'madre'     => ['enabled' => false, 'data' => $this->emptyGuardian()],
            'encargado' => ['enabled' => false, 'data' => $this->emptyGuardian()],
        ];
    }

    private function saveGuardian(Student $student, string $key, array $data): void
    {
        $relationshipMap  = ['padre' => 'Papá', 'madre' => 'Mamá', 'encargado' => 'Encargado'];
        $relationshipType = $relationshipMap[$key];

        $currentGuardian = $student->guardians()
            ->wherePivot('relationship_type', $relationshipType)
            ->first();

        $guardianData = [
            'first_name'        => $data['first_name'],
            'last_name'         => $data['last_name'],
            'birthplace'        => $data['birthplace']        ?: null,
            'birthdate'         => $data['birthdate'],
            'nationality'       => $data['nationality'],
            'cui'               => $data['cui'],
            'cui_extended_in'   => $data['cui_extended_in'],
            'profession'        => $data['profession'],
            'residence_address' => $data['residence_address'],
            'phone'             => $data['phone'],
            'email'             => $data['email']             ?: null,
            'company_name'      => $data['company_name']      ?: null,
            'company_address'   => $data['company_address']   ?: null,
            'company_phone'     => $data['company_phone']     ?: null,
        ];

        $guardian = Guardian::where('cui', $data['cui'])->first();

        if ($guardian) {
            $guardian->update($guardianData);
        } else {
            $guardian = Guardian::create($guardianData);
        }

        // Si ya está vinculado y es el mismo, solo actualizar datos (ya hecho arriba)
        if ($currentGuardian && $currentGuardian->id === $guardian->id) {
            return;
        }

        // Desvincular el anterior si es diferente
        if ($currentGuardian) {
            $otherStudents = $currentGuardian->students()
                ->where('students.id', '!=', $student->id)
                ->count();
            $student->guardians()->detach($currentGuardian->id);
            if ($otherStudents === 0) {
                $currentGuardian->delete();
            }
        }

        $student->guardians()->attach($guardian->id, ['relationship_type' => $relationshipType]);
    }

    private function removeGuardian(Student $student, string $key): void
    {
        $relationshipMap  = ['padre' => 'Papá', 'madre' => 'Mamá', 'encargado' => 'Encargado'];
        $relationshipType = $relationshipMap[$key];

        $guardian = $student->guardians()
            ->wherePivot('relationship_type', $relationshipType)
            ->first();

        if (! $guardian) {
            return;
        }

        $otherStudents = $guardian->students()
            ->where('students.id', '!=', $student->id)
            ->count();

        $student->guardians()->detach($guardian->id);

        if ($otherStudents === 0) {
            $guardian->delete();
        }
    }

    public function mount(string $token): void
    {
        $data = Cache::get("student_update_{$token}");

        if (! $data) {
            $this->tokenExpired = true;

            return;
        }

        $this->token      = $token;
        $this->emailNuevo = $data['email_nuevo'];
        $this->studentId  = $data['student_id'];

        $student = Student::with('user', 'guardians')->findOrFail($this->studentId);
        $user    = $student->user;

        // Campos de solo lectura
        $this->cui           = $user->cui;
        $this->firstName     = $user->first_name;
        $this->middleName    = $user->middle_name    ?? '';
        $this->surname       = $user->surname;
        $this->secondSurname = $user->second_surname ?? '';

        // Email pre-llenado desde el paso 1
        $this->email = $this->emailNuevo;

        // Inicializar y cargar guardianes existentes
        $this->resetGuardians();

        $reverseMap = ['Papá' => 'padre', 'Mamá' => 'madre', 'Encargado' => 'encargado'];

        foreach ($student->guardians as $guardian) {
            $key = $reverseMap[$guardian->pivot->relationship_type] ?? null;
            if (! $key) {
                continue;
            }

            $this->guardians[$key] = [
                'enabled' => true,
                'data'    => [
                    'first_name'        => $guardian->first_name,
                    'last_name'         => $guardian->last_name,
                    'birthplace'        => $guardian->birthplace        ?? '',
                    'birthdate'         => optional($guardian->birthdate)->format('Y-m-d') ?? '',
                    'nationality'       => $guardian->nationality       ?? '',
                    'cui'               => $guardian->cui               ?? '',
                    'cui_extended_in'   => $guardian->cui_extended_in   ?? '',
                    'profession'        => $guardian->profession        ?? '',
                    'residence_address' => $guardian->residence_address ?? '',
                    'phone'             => $guardian->phone             ?? '',
                    'email'             => $guardian->email             ?? '',
                    'company_name'      => $guardian->company_name      ?? '',
                    'company_address'   => $guardian->company_address   ?? '',
                    'company_phone'     => $guardian->company_phone     ?? '',
                ],
            ];
        }

        $this->originalGuardianKeys = array_keys(
            array_filter($this->guardians, fn($g) => $g['enabled'])
        );
    }

    public function updatedEncargadoRole(string $value): void
    {
        if ($value === 'estudiante') {
            $this->guardians['encargado']['enabled'] = true;
            $this->guardians['encargado']['data']    = array_merge($this->emptyGuardian(), [
                'first_name'        => trim($this->firstName . ' ' . $this->middleName),
                'last_name'         => trim($this->surname . ' ' . $this->secondSurname),
                'cui'               => $this->cui,
                'birthdate'         => $this->birthdate,
                'phone'             => $this->cellphone,
                'email'             => $this->email,
                'residence_address' => $this->address,
            ]);
        } elseif ($value === 'padre' || $value === 'madre') {
            $this->guardians['encargado']['enabled'] = true;
            $this->guardians['encargado']['data']    = $this->guardians[$value]['data'];
        } else {
            $this->guardians['encargado']['data'] = $this->emptyGuardian();
        }
    }

    public function confirmDeleteAndSave(): void
    {
        $this->confirmingDelete  = true;
        $this->showDeleteWarning = false;
        $this->save();
    }

    public function cancelDeleteWarning(): void
    {
        $this->showDeleteWarning  = false;
        $this->pendingDeleteLabels = [];
    }

    public function save(): void
    {
        $rules    = [
            'birthdate' => ['required', 'date'],
            'gender'    => ['required', 'in:Masculino,Femenino'],
            'weight'    => ['nullable', 'numeric', 'min:0', 'max:500'],
            'height'    => ['nullable', 'numeric', 'min:0', 'max:3'],
        ];
        $messages = [
            'birthdate.required' => 'La fecha de nacimiento es obligatoria.',
            'gender.required'    => 'El género es obligatorio.',
            'weight.numeric'     => 'El peso debe ser un número.',
            'height.numeric'     => 'La estatura debe ser un número.',
        ];

        $labelMap = ['padre' => 'Padre', 'madre' => 'Madre', 'encargado' => 'Encargado'];

        foreach (['padre', 'madre', 'encargado'] as $key) {
            if (! $this->guardians[$key]['enabled']) {
                continue;
            }

            $label = $labelMap[$key];

            $rules["guardians.$key.data.first_name"]        = ['required', 'string', 'max:255'];
            $rules["guardians.$key.data.last_name"]         = ['required', 'string', 'max:255'];
            $rules["guardians.$key.data.birthdate"]         = ['required', 'date'];
            $rules["guardians.$key.data.nationality"]       = ['required', 'string', 'max:100'];
            $rules["guardians.$key.data.cui"]               = ['required', 'string', 'max:20'];
            $rules["guardians.$key.data.cui_extended_in"]   = ['required', 'string', 'max:100'];
            $rules["guardians.$key.data.profession"]        = ['required', 'string', 'max:100'];
            $rules["guardians.$key.data.residence_address"] = ['required', 'string', 'max:255'];
            $rules["guardians.$key.data.phone"]             = ['required', 'string', 'max:30'];

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

        $this->validate($rules, $messages);

        // Verificar si hay guardianes que se eliminarán y aún no fue confirmado
        if (! $this->confirmingDelete) {
            $toDelete = [];
            foreach ($this->originalGuardianKeys as $key) {
                if (! $this->guardians[$key]['enabled']) {
                    $toDelete[] = $labelMap[$key];
                }
            }

            if (! empty($toDelete)) {
                $this->pendingDeleteLabels = $toDelete;
                $this->showDeleteWarning   = true;

                return;
            }
        }

        $this->confirmingDelete  = false;
        $this->showDeleteWarning = false;

        // Re-verificar token (defensa contra expiración durante el llenado)
        $cached = Cache::get("student_update_{$this->token}");

        if (! $cached) {
            $this->tokenExpired = true;

            return;
        }

        $student = Student::with('user', 'guardians')->findOrFail($this->studentId);
        $user    = $student->user;

        DB::transaction(function () use ($student, $user): void {
            // 1. Actualizar User
            $user->update([
                'married_surname'      => $this->marriedSurname ?: null,
                'civil_status'         => $this->civilStatus    ?: null,
                'birthdate'            => $this->birthdate,
                'gender'               => $this->gender,
                'email'                => $this->emailNuevo,
                'personal_email'       => $this->emailNuevo,
                'cellphone'            => $this->cellphone       ?: null,
                'address'              => $this->address         ?: null,
                'password'             => Hash::make('password'),
                'is_active'            => true,
                'must_change_password' => true,
            ]);

            // 2. Ficha Médica
            MedicalRecord::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'takes_medication'       => $this->takesMedication,
                    'medication_description' => $this->takesMedication ? ($this->medicationDescription ?: null) : null,
                    'has_disease'            => $this->hasDisease,
                    'disease_description'    => $this->hasDisease      ? ($this->diseaseDescription    ?: null) : null,
                    'has_allergies'          => $this->hasAllergies,
                    'allergies_description'  => $this->hasAllergies    ? ($this->allergiesDescription  ?: null) : null,
                    'had_surgery'            => $this->hadSurgery,
                    'surgery_description'    => $this->hadSurgery      ? ($this->surgeryDescription    ?: null) : null,
                    'blood_type'             => $this->bloodType        ?: null,
                    'weight'                 => $this->weight           ?: null,
                    'height'                 => $this->height           ?: null,
                ]
            );

            // 3. Guardianes — guardar habilitados, eliminar los originales que quedaron desactivados
            foreach (['padre', 'madre', 'encargado'] as $key) {
                if ($this->guardians[$key]['enabled']) {
                    $this->saveGuardian($student, $key, $this->guardians[$key]['data']);
                } elseif (in_array($key, $this->originalGuardianKeys)) {
                    $this->removeGuardian($student, $key);
                }
            }

            // 4. Registrar actualización completada
            StudentDataUpdate::create([
                'student_id'   => $student->id,
                'year'         => now()->year,
                'completed_at' => now(),
                'ip_address'   => Request::ip(),
            ]);

            // 5. Invalidar el token
            Cache::forget("student_update_{$this->token}");

            // 6. Auditoría
            AuditService::log(
                event: 'updated',
                module: 'Actualización QR',
                description: "Estudiante \"{$user->name}\" actualizó sus datos via QR",
                auditable: $student,
                userId: $user->id,
            );
        });

        $this->redirectRoute('student.data.done');
    }

    public function render()
    {
        return view('livewire.student-data-update-form');
    }
}
