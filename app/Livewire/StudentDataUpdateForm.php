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

    // ── Tab 3 — Encargado ─────────────────────────────────────────────────────
    public string $guardianFirstName = '';

    public string $guardianLastName = '';

    public string $guardianBirthplace = '';

    public string $guardianBirthdate = '';

    public string $guardianNationality = '';

    public string $guardianCui = '';

    public string $guardianCuiExtendedIn = '';

    public string $guardianProfession = '';

    public string $guardianResidenceAddress = '';

    public string $guardianPhone = '';

    public string $guardianEmail = '';

    public string $guardianCompanyName = '';

    public string $guardianCompanyAddress = '';

    public string $guardianCompanyPhone = '';

    public function mount(string $token): void
    {
        $data = Cache::get("student_update_{$token}");

        if (! $data) {
            $this->tokenExpired = true;

            return;
        }

        $this->token = $token;
        $this->emailNuevo = $data['email_nuevo'];
        $this->studentId = $data['student_id'];

        $student = Student::with('user')->findOrFail($this->studentId);
        $user = $student->user;

        // Campos de solo lectura
        $this->cui = $user->cui;
        $this->firstName = $user->first_name;
        $this->middleName = $user->middle_name ?? '';
        $this->surname = $user->surname;
        $this->secondSurname = $user->second_surname ?? '';

        // Email pre-llenado desde el paso 1
        $this->email = $this->emailNuevo;
    }

    public function save(): void
    {
        $this->validate([
            // Datos personales editables
            'birthdate' => ['required', 'date'],
            'gender' => ['required', 'in:Masculino,Femenino'],
            // Ficha médica (todos opcionales salvo los booleanos)
            'weight' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'height' => ['nullable', 'numeric', 'min:0', 'max:3'],
            // Encargado — campos obligatorios
            'guardianFirstName' => ['required', 'string', 'max:255'],
            'guardianLastName' => ['required', 'string', 'max:255'],
            'guardianBirthdate' => ['required', 'date'],
            'guardianNationality' => ['required', 'string', 'max:100'],
            'guardianCui' => ['required', 'string', 'max:20'],
            'guardianCuiExtendedIn' => ['required', 'string', 'max:100'],
            'guardianProfession' => ['required', 'string', 'max:100'],
            'guardianResidenceAddress' => ['required', 'string', 'max:255'],
            'guardianPhone' => ['required', 'string', 'max:30'],
        ], [
            'birthdate.required' => 'La fecha de nacimiento es obligatoria.',
            'gender.required' => 'El género es obligatorio.',
            'weight.numeric' => 'El peso debe ser un número.',
            'height.numeric' => 'La estatura debe ser un número.',
            'guardianFirstName.required' => 'El nombre del encargado es obligatorio.',
            'guardianLastName.required' => 'El apellido del encargado es obligatorio.',
            'guardianBirthdate.required' => 'La fecha de nacimiento del encargado es obligatoria.',
            'guardianNationality.required' => 'La nacionalidad del encargado es obligatoria.',
            'guardianCui.required' => 'El CUI del encargado es obligatorio.',
            'guardianCuiExtendedIn.required' => 'El lugar de extensión del CUI es obligatorio.',
            'guardianProfession.required' => 'La profesión del encargado es obligatoria.',
            'guardianResidenceAddress.required' => 'La dirección del encargado es obligatoria.',
            'guardianPhone.required' => 'El teléfono del encargado es obligatorio.',
        ]);

        // Re-verificar token (defensa contra expiración durante el llenado)
        $cached = Cache::get("student_update_{$this->token}");

        if (! $cached) {
            $this->tokenExpired = true;

            return;
        }

        $student = Student::with('user', 'guardians')->findOrFail($this->studentId);
        $user = $student->user;

        DB::transaction(function () use ($student, $user): void {
            // 1. Actualizar User
            $user->update([
                'married_surname' => $this->marriedSurname ?: null,
                'civil_status' => $this->civilStatus ?: null,
                'birthdate' => $this->birthdate,
                'gender' => $this->gender,
                'email' => $this->emailNuevo,
                'personal_email' => $this->emailNuevo,
                'cellphone' => $this->cellphone ?: null,
                'address' => $this->address ?: null,
                'password' => Hash::make('password'),
                'is_active' => true,
                'must_change_password' => true,
            ]);

            // 2. Ficha Médica
            MedicalRecord::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'takes_medication' => $this->takesMedication,
                    'medication_description' => $this->takesMedication ? ($this->medicationDescription ?: null) : null,
                    'has_disease' => $this->hasDisease,
                    'disease_description' => $this->hasDisease ? ($this->diseaseDescription ?: null) : null,
                    'has_allergies' => $this->hasAllergies,
                    'allergies_description' => $this->hasAllergies ? ($this->allergiesDescription ?: null) : null,
                    'had_surgery' => $this->hadSurgery,
                    'surgery_description' => $this->hadSurgery ? ($this->surgeryDescription ?: null) : null,
                    'blood_type' => $this->bloodType ?: null,
                    'weight' => $this->weight ?: null,
                    'height' => $this->height ?: null,
                ]
            );

            // 3. Encargado (actualizar el primero o crear uno nuevo)
            $guardianData = [
                'first_name' => $this->guardianFirstName,
                'last_name' => $this->guardianLastName,
                'birthplace' => $this->guardianBirthplace ?: null,
                'birthdate' => $this->guardianBirthdate,
                'nationality' => $this->guardianNationality,
                'cui' => $this->guardianCui,
                'cui_extended_in' => $this->guardianCuiExtendedIn,
                'profession' => $this->guardianProfession,
                'residence_address' => $this->guardianResidenceAddress,
                'phone' => $this->guardianPhone,
                'email' => $this->guardianEmail ?: null,
                'company_name' => $this->guardianCompanyName ?: null,
                'company_address' => $this->guardianCompanyAddress ?: null,
                'company_phone' => $this->guardianCompanyPhone ?: null,
            ];

            $existingGuardian = $student->guardians()->first();

            if ($existingGuardian) {
                $existingGuardian->update($guardianData);
            } else {
                $newGuardian = Guardian::create($guardianData);
                $student->guardians()->attach($newGuardian->id, ['relationship_type' => 'Encargado']);
            }

            // 4. Registrar actualización completada
            StudentDataUpdate::create([
                'student_id' => $student->id,
                'year' => now()->year,
                'completed_at' => now(),
                'ip_address' => Request::ip(),
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
