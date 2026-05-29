<?php

namespace App\Livewire;

use App\Models\AdmissionApplication;
use App\Models\Grade;
use App\Models\Level;
use App\Models\SystemSetting;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class AdmissionForm extends Component
{
    // Datos del alumno
    public string $studentFirstName = '';

    public string $studentSecondName = '';

    public string $studentFirstSurname = '';

    public string $studentSecondSurname = '';

    public string $studentBirthdate = '';

    public string $studentAddress = '';

    public string $studentPreviousSchool = '';

    public string $studentReligion = '';

    // Grado al que aplica
    public string $year = '';

    public string $levelId = '';

    public string $gradeId = '';

    // Padre
    public bool $fatherEnabled = true;

    public string $fatherFirstName = '';

    public string $fatherLastName = '';

    public string $fatherPhone = '';

    public string $fatherWorkplace = '';

    public string $fatherNit = '';

    public string $fatherProfession = '';

    // Madre
    public bool $motherEnabled = true;

    public string $motherFirstName = '';

    public string $motherLastName = '';

    public string $motherPhone = '';

    public string $motherWorkplace = '';

    public string $motherNit = '';

    public string $motherProfession = '';

    // Encargado
    public string $guardianType = '';

    public string $guardianName = '';

    public string $guardianPhone = '';

    public string $guardianNit = '';

    public string $guardianEmail = '';

    // Familia
    public string $sonsCount = '';

    public string $sonsAges = '';

    public string $daughtersCount = '';

    public string $daughtersAges = '';

    // Cómo nos conoció
    public string $referralSource = '';

    public function mount(): void
    {
        $this->year = now()->month <= 6
            ? (string) now()->year
            : (string) now()->addYear()->year;
    }

    public function updatedGuardianType(): void
    {
        $this->guardianName = match ($this->guardianType) {
            'father' => trim($this->fatherFirstName.' '.$this->fatherLastName),
            'mother' => trim($this->motherFirstName.' '.$this->motherLastName),
            default => '',
        };
        $this->guardianPhone = match ($this->guardianType) {
            'father' => $this->fatherPhone,
            'mother' => $this->motherPhone,
            default => '',
        };
        // NIT solo aplica al encargado "otro"
        if ($this->guardianType !== 'other') {
            $this->guardianNit = '';
        }
    }

    public function updatedFatherEnabled(): void
    {
        if (! $this->fatherEnabled) {
            $this->fatherFirstName = '';
            $this->fatherLastName = '';
            $this->fatherPhone = '';
            $this->fatherWorkplace = '';
            $this->fatherNit = '';
            $this->fatherProfession = '';

            if ($this->guardianType === 'father') {
                $this->guardianType = '';
                $this->guardianName = '';
                $this->guardianPhone = '';
            }
        }
    }

    public function updatedMotherEnabled(): void
    {
        if (! $this->motherEnabled) {
            $this->motherFirstName = '';
            $this->motherLastName = '';
            $this->motherPhone = '';
            $this->motherWorkplace = '';
            $this->motherNit = '';
            $this->motherProfession = '';

            if ($this->guardianType === 'mother') {
                $this->guardianType = '';
                $this->guardianName = '';
                $this->guardianPhone = '';
            }
        }
    }

    public function updatedLevelId(): void
    {
        $this->gradeId = '';
        unset($this->grades);
    }

    #[Computed]
    public function isAdmissionsOpen(): bool
    {
        return SystemSetting::get('enrollment_mode', 'direct') === 'admissions';
    }

    #[Computed]
    public function levels(): Collection
    {
        return Level::orderBy('ordering')->get();
    }

    #[Computed]
    public function grades(): Collection
    {
        if (! $this->levelId) {
            return collect();
        }

        $grades = Grade::whereHas('classrooms', fn ($q) => $q->where('level_id', $this->levelId))
            ->orderBy('ordering')
            ->get();

        return $grades->isNotEmpty() ? $grades : Grade::orderBy('ordering')->get();
    }

    #[Computed]
    public function availableYears(): array
    {
        $current = now()->year;

        // Enero–junio: año actual + siguiente | Julio–diciembre: solo el siguiente
        return now()->month <= 6
            ? [$current, $current + 1]
            : [$current + 1];
    }

    protected function rules(): array
    {
        return [
            // Alumno
            'studentFirstName' => ['required', 'string', 'max:100'],
            'studentSecondName' => ['nullable', 'string', 'max:100'],
            'studentFirstSurname' => ['required', 'string', 'max:100'],
            'studentSecondSurname' => ['nullable', 'string', 'max:100'],
            'studentBirthdate' => ['required', 'date', 'before:today'],
            'studentAddress' => ['required', 'string', 'max:255'],
            'studentPreviousSchool' => ['nullable', 'string', 'max:255'],
            'studentReligion' => ['nullable', 'string', 'max:100'],
            // Grado
            'year' => ['required', 'integer'],
            'levelId' => ['required', 'exists:levels,id'],
            'gradeId' => ['required', 'exists:grades,id'],
            // Padre — solo requerido si está habilitado
            'fatherFirstName' => $this->fatherEnabled ? ['required', 'string', 'max:100'] : ['nullable'],
            'fatherLastName' => $this->fatherEnabled ? ['required', 'string', 'max:100'] : ['nullable'],
            'fatherPhone' => $this->fatherEnabled ? ['required', 'string', 'max:20'] : ['nullable'],
            'fatherWorkplace' => ['nullable', 'string', 'max:255'],
            'fatherNit' => ['nullable', 'string', 'max:20'],
            'fatherProfession' => ['nullable', 'string', 'max:100'],
            // Madre — solo requerido si está habilitada
            'motherFirstName' => $this->motherEnabled ? ['required', 'string', 'max:100'] : ['nullable'],
            'motherLastName' => $this->motherEnabled ? ['required', 'string', 'max:100'] : ['nullable'],
            'motherPhone' => $this->motherEnabled ? ['required', 'string', 'max:20'] : ['nullable'],
            'motherWorkplace' => ['nullable', 'string', 'max:255'],
            'motherNit' => ['nullable', 'string', 'max:20'],
            'motherProfession' => ['nullable', 'string', 'max:100'],
            // Encargado
            'guardianType' => ['required', 'in:father,mother,other'],
            'guardianName' => ['required', 'string', 'max:200'],
            'guardianPhone' => ['required', 'string', 'max:20'],
            'guardianNit' => ['nullable', 'string', 'max:20'],
            'guardianEmail' => ['required', 'email', 'max:255'],
            // Familia
            'sonsCount' => ['nullable', 'integer', 'min:0'],
            'sonsAges' => ['nullable', 'string', 'max:100'],
            'daughtersCount' => ['nullable', 'integer', 'min:0'],
            'daughtersAges' => ['nullable', 'string', 'max:100'],
            // Referido
            'referralSource' => ['nullable', 'string', 'max:100'],
        ];
    }

    protected function messages(): array
    {
        return [
            'studentFirstName.required' => 'El primer nombre del alumno es requerido.',
            'studentFirstSurname.required' => 'El primer apellido del alumno es requerido.',
            'studentBirthdate.required' => 'La fecha de nacimiento es requerida.',
            'studentBirthdate.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
            'studentAddress.required' => 'La dirección es requerida.',
            'year.required' => 'El ciclo escolar es requerido.',
            'levelId.required' => 'Seleccione el nivel educativo.',
            'levelId.exists' => 'Seleccione un nivel válido.',
            'gradeId.required' => 'Seleccione el grado.',
            'gradeId.exists' => 'Seleccione un grado válido.',
            'fatherFirstName.required' => 'El nombre del padre es requerido.',
            'fatherLastName.required' => 'Los apellidos del padre son requeridos.',
            'fatherPhone.required' => 'El teléfono del padre es requerido.',
            'motherFirstName.required' => 'El nombre de la madre es requerido.',
            'motherLastName.required' => 'Los apellidos de la madre son requeridos.',
            'motherPhone.required' => 'El teléfono de la madre es requerido.',
            'guardianType.required' => 'Seleccione quién es el encargado del alumno.',
            'guardianType.in' => 'El encargado debe ser padre, madre u otro.',
            'guardianName.required' => 'El nombre del encargado es requerido.',
            'guardianPhone.required' => 'El teléfono del encargado es requerido.',
            'guardianEmail.required' => 'El correo del encargado es requerido.',
            'guardianEmail.email' => 'Ingrese una dirección de correo electrónico válida.',
        ];
    }

    public function submit(): void
    {
        $this->validate();

        $app = AdmissionApplication::create([
            'year' => $this->year,
            'level_id' => $this->levelId,
            'grade_id' => $this->gradeId,
            'student_first_name' => $this->studentFirstName,
            'student_second_name' => $this->studentSecondName ?: null,
            'student_first_surname' => $this->studentFirstSurname,
            'student_second_surname' => $this->studentSecondSurname ?: null,
            'student_birthdate' => $this->studentBirthdate,
            'student_address' => $this->studentAddress,
            'student_previous_school' => $this->studentPreviousSchool ?: null,
            'student_religion' => $this->studentReligion ?: null,
            'father_first_name' => $this->fatherEnabled ? $this->fatherFirstName : null,
            'father_last_name' => $this->fatherEnabled ? $this->fatherLastName : null,
            'father_phone' => $this->fatherEnabled ? $this->fatherPhone : null,
            'father_workplace' => $this->fatherEnabled ? ($this->fatherWorkplace ?: null) : null,
            'father_nit' => $this->fatherEnabled ? ($this->fatherNit ?: null) : null,
            'father_profession' => $this->fatherEnabled ? ($this->fatherProfession ?: null) : null,
            'mother_first_name' => $this->motherEnabled ? $this->motherFirstName : null,
            'mother_last_name' => $this->motherEnabled ? $this->motherLastName : null,
            'mother_phone' => $this->motherEnabled ? $this->motherPhone : null,
            'mother_workplace' => $this->motherEnabled ? ($this->motherWorkplace ?: null) : null,
            'mother_nit' => $this->motherEnabled ? ($this->motherNit ?: null) : null,
            'mother_profession' => $this->motherEnabled ? ($this->motherProfession ?: null) : null,
            'guardian_type' => $this->guardianType,
            'guardian_name' => $this->guardianName,
            'guardian_phone' => $this->guardianPhone,
            'guardian_nit' => $this->guardianType === 'other' ? ($this->guardianNit ?: null) : null,
            'guardian_email' => $this->guardianEmail,
            'referral_source' => $this->referralSource ?: null,
            'sons_count' => $this->sonsCount !== '' ? (int) $this->sonsCount : null,
            'sons_ages' => $this->sonsAges ?: null,
            'daughters_count' => $this->daughtersCount !== '' ? (int) $this->daughtersCount : null,
            'daughters_ages' => $this->daughtersAges ?: null,
            'current_status' => 'pending',
            'ip_address' => request()->ip(),
        ]);

        $app->statuses()->create(['status' => 'pending']);
        $app->documents()->create([]);

        $this->redirect(route('admissions.done'));
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.admission-form')
            ->extends('layouts.public')
            ->section('content');
    }
}
