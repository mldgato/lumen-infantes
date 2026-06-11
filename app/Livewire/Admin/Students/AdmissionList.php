<?php

namespace App\Livewire\Admin\Students;

use App\Models\AdmissionApplication;
use App\Models\AdmissionApplicationDocument;
use App\Models\Grade;
use App\Models\Level;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class AdmissionList extends Component
{
    use WithPagination;

    // ── Filtros de lista ─────────────────────────────────────────
    public string $search = '';

    public string $filterYear = '';

    public string $filterStatus = '';

    public int $cant = 15;

    protected $queryString = ['search', 'filterYear', 'filterStatus', 'cant'];

    // ── Modal de detalle / edición ───────────────────────────────
    public ?AdmissionApplication $viewing = null;

    // Datos del alumno
    public string $editStudentFirstName = '';

    public string $editStudentSecondName = '';

    public string $editStudentFirstSurname = '';

    public string $editStudentSecondSurname = '';

    public string $editStudentBirthdate = '';

    public string $editStudentAddress = '';

    public string $editStudentPreviousSchool = '';

    public string $editStudentReligion = '';

    // Grado
    public string $editYear = '';

    public string $editLevelId = '';

    public string $editGradeId = '';

    // Padre
    public bool $editFatherEnabled = true;

    public string $editFatherFirstName = '';

    public string $editFatherLastName = '';

    public string $editFatherPhone = '';

    public string $editFatherWorkplace = '';

    public string $editFatherNit = '';

    public string $editFatherProfession = '';

    // Madre
    public bool $editMotherEnabled = true;

    public string $editMotherFirstName = '';

    public string $editMotherLastName = '';

    public string $editMotherPhone = '';

    public string $editMotherWorkplace = '';

    public string $editMotherNit = '';

    public string $editMotherProfession = '';

    // Encargado
    public string $editGuardianType = '';

    public string $editGuardianName = '';

    public string $editGuardianPhone = '';

    public string $editGuardianNit = '';

    public string $editGuardianEmail = '';

    // Familia
    public string $editSonsCount = '';

    public string $editSonsAges = '';

    public string $editDaughtersCount = '';

    public string $editDaughtersAges = '';

    // Cómo nos conoció + URLs
    public string $editReferralSource = '';

    public string $editUrlDocuments = '';

    public string $editUrlPayment = '';

    // Rechazo con notas
    public ?int $rejectingId = null;

    public string $rejectionNotes = '';

    // ── Mount ────────────────────────────────────────────────────
    public function mount(): void
    {
        $this->authorize('admin.admissions.index');
        $this->filterYear = (string) now()->year;
    }

    // ── Reseteo de paginación ────────────────────────────────────
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatingFilterYear(): void
    {
        $this->resetPage();
    }

    // ── Cascading en formulario de edición ───────────────────────
    public function updatedEditLevelId(): void
    {
        $this->editGradeId = '';
        unset($this->editGrades);
    }

    public function updatedEditFatherEnabled(): void
    {
        if (! $this->editFatherEnabled) {
            $this->editFatherFirstName = '';
            $this->editFatherLastName = '';
            $this->editFatherPhone = '';
            $this->editFatherWorkplace = '';
            $this->editFatherNit = '';
            $this->editFatherProfession = '';

            if ($this->editGuardianType === 'father') {
                $this->editGuardianType = '';
                $this->editGuardianName = '';
                $this->editGuardianPhone = '';
            }
        }
    }

    public function updatedEditMotherEnabled(): void
    {
        if (! $this->editMotherEnabled) {
            $this->editMotherFirstName = '';
            $this->editMotherLastName = '';
            $this->editMotherPhone = '';
            $this->editMotherWorkplace = '';
            $this->editMotherNit = '';
            $this->editMotherProfession = '';

            if ($this->editGuardianType === 'mother') {
                $this->editGuardianType = '';
                $this->editGuardianName = '';
                $this->editGuardianPhone = '';
            }
        }
    }

    public function updatedEditGuardianType(): void
    {
        $this->editGuardianName = match ($this->editGuardianType) {
            'father' => trim($this->editFatherFirstName.' '.$this->editFatherLastName),
            'mother' => trim($this->editMotherFirstName.' '.$this->editMotherLastName),
            default => '',
        };
        $this->editGuardianPhone = match ($this->editGuardianType) {
            'father' => $this->editFatherPhone,
            'mother' => $this->editMotherPhone,
            default => '',
        };
        if ($this->editGuardianType !== 'other') {
            $this->editGuardianNit = '';
        }
    }

    // ── Computed ─────────────────────────────────────────────────
    #[Computed]
    public function applications()
    {
        return AdmissionApplication::with(['level', 'grade'])
            ->when($this->filterYear, fn ($q) => $q->where('year', $this->filterYear))
            ->when($this->filterStatus, fn ($q) => $q->where('current_status', $this->filterStatus))
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('student_first_name', 'like', "%{$this->search}%")
                        ->orWhere('student_first_surname', 'like', "%{$this->search}%")
                        ->orWhere('guardian_email', 'like', "%{$this->search}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate($this->cant);
    }

    #[Computed]
    public function availableYears(): array
    {
        $years = AdmissionApplication::selectRaw('DISTINCT year')
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        return empty($years) ? [now()->year, now()->addYear()->year] : $years;
    }

    #[Computed]
    public function pendingCount(): int
    {
        return AdmissionApplication::where('current_status', 'pending')
            ->when($this->filterYear, fn ($q) => $q->where('year', $this->filterYear))
            ->count();
    }

    #[Computed]
    public function allLevels(): Collection
    {
        return Level::orderBy('ordering')->get();
    }

    #[Computed]
    public function editGrades(): Collection
    {
        if (! $this->editLevelId) {
            return collect();
        }

        $grades = Grade::whereHas('classrooms', fn ($q) => $q->where('level_id', $this->editLevelId))
            ->orderBy('ordering')
            ->get();

        return $grades->isNotEmpty() ? $grades : Grade::orderBy('ordering')->get();
    }

    // ── Ver / Editar solicitud ───────────────────────────────────
    public function viewApplication(int $id): void
    {
        $this->viewing = AdmissionApplication::with(['level', 'grade', 'statuses.user', 'documents'])
            ->findOrFail($id);

        if (! $this->viewing->documents) {
            $this->viewing->documents()->create([]);
            $this->viewing->load('documents');
        }

        $this->loadEditFields();
        $this->dispatch('openAdmissionDetailModal');
    }

    private function loadEditFields(): void
    {
        $a = $this->viewing;

        $this->editStudentFirstName = $a->student_first_name;
        $this->editStudentSecondName = $a->student_second_name ?? '';
        $this->editStudentFirstSurname = $a->student_first_surname;
        $this->editStudentSecondSurname = $a->student_second_surname ?? '';
        $this->editStudentBirthdate = $a->student_birthdate?->format('Y-m-d') ?? '';
        $this->editStudentAddress = $a->student_address;
        $this->editStudentPreviousSchool = $a->student_previous_school ?? '';
        $this->editStudentReligion = $a->student_religion ?? '';
        $this->editYear = (string) $a->year;
        $this->editLevelId = (string) $a->level_id;
        $this->editGradeId = (string) $a->grade_id;

        $this->editFatherEnabled = $a->father_first_name !== null;
        $this->editFatherFirstName = $a->father_first_name ?? '';
        $this->editFatherLastName = $a->father_last_name ?? '';
        $this->editFatherPhone = $a->father_phone ?? '';
        $this->editFatherWorkplace = $a->father_workplace ?? '';
        $this->editFatherNit = $a->father_nit ?? '';
        $this->editFatherProfession = $a->father_profession ?? '';

        $this->editMotherEnabled = $a->mother_first_name !== null;
        $this->editMotherFirstName = $a->mother_first_name ?? '';
        $this->editMotherLastName = $a->mother_last_name ?? '';
        $this->editMotherPhone = $a->mother_phone ?? '';
        $this->editMotherWorkplace = $a->mother_workplace ?? '';
        $this->editMotherNit = $a->mother_nit ?? '';
        $this->editMotherProfession = $a->mother_profession ?? '';

        $this->editGuardianType = $a->guardian_type;
        $this->editGuardianName = $a->guardian_name;
        $this->editGuardianPhone = $a->guardian_phone ?? '';
        $this->editGuardianNit = $a->guardian_nit ?? '';
        $this->editGuardianEmail = $a->guardian_email;

        $this->editSonsCount = $a->sons_count !== null ? (string) $a->sons_count : '';
        $this->editSonsAges = $a->sons_ages ?? '';
        $this->editDaughtersCount = $a->daughters_count !== null ? (string) $a->daughters_count : '';
        $this->editDaughtersAges = $a->daughters_ages ?? '';

        $this->editReferralSource = $a->referral_source ?? '';
        $this->editUrlDocuments = $a->url_documents ?? '';
        $this->editUrlPayment = $a->url_payment ?? '';

        unset($this->editGrades);
    }

    protected function editRules(): array
    {
        return [
            'editStudentFirstName' => ['required', 'string', 'max:100'],
            'editStudentSecondName' => ['nullable', 'string', 'max:100'],
            'editStudentFirstSurname' => ['required', 'string', 'max:100'],
            'editStudentSecondSurname' => ['nullable', 'string', 'max:100'],
            'editStudentBirthdate' => ['required', 'date', 'before:today'],
            'editStudentAddress' => ['required', 'string', 'max:255'],
            'editStudentPreviousSchool' => ['nullable', 'string', 'max:255'],
            'editStudentReligion' => ['nullable', 'string', 'max:100'],
            'editYear' => ['required', 'integer'],
            'editLevelId' => ['required', 'exists:levels,id'],
            'editGradeId' => ['required', 'exists:grades,id'],
            'editFatherFirstName' => $this->editFatherEnabled ? ['required', 'string', 'max:100'] : ['nullable'],
            'editFatherLastName' => $this->editFatherEnabled ? ['required', 'string', 'max:100'] : ['nullable'],
            'editFatherPhone' => $this->editFatherEnabled ? ['required', 'string', 'max:20'] : ['nullable'],
            'editFatherWorkplace' => ['nullable', 'string', 'max:255'],
            'editFatherNit' => ['nullable', 'string', 'max:20'],
            'editFatherProfession' => ['nullable', 'string', 'max:100'],
            'editMotherFirstName' => $this->editMotherEnabled ? ['required', 'string', 'max:100'] : ['nullable'],
            'editMotherLastName' => $this->editMotherEnabled ? ['required', 'string', 'max:100'] : ['nullable'],
            'editMotherPhone' => $this->editMotherEnabled ? ['required', 'string', 'max:20'] : ['nullable'],
            'editMotherWorkplace' => ['nullable', 'string', 'max:255'],
            'editMotherNit' => ['nullable', 'string', 'max:20'],
            'editMotherProfession' => ['nullable', 'string', 'max:100'],
            'editGuardianType' => ['required', 'in:father,mother,other'],
            'editGuardianName' => ['required', 'string', 'max:200'],
            'editGuardianPhone' => ['required', 'string', 'max:20'],
            'editGuardianNit' => ['nullable', 'string', 'max:20'],
            'editGuardianEmail' => ['required', 'email', 'max:255'],
            'editSonsCount' => ['nullable', 'integer', 'min:0'],
            'editSonsAges' => ['nullable', 'string', 'max:100'],
            'editDaughtersCount' => ['nullable', 'integer', 'min:0'],
            'editDaughtersAges' => ['nullable', 'string', 'max:100'],
            'editReferralSource' => ['nullable', 'string', 'max:100'],
            'editUrlDocuments' => ['nullable', 'string', 'max:1000'],
            'editUrlPayment' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function editMessages(): array
    {
        return [
            'editStudentFirstName.required' => 'El primer nombre del alumno es requerido.',
            'editStudentFirstSurname.required' => 'El primer apellido del alumno es requerido.',
            'editStudentBirthdate.required' => 'La fecha de nacimiento es requerida.',
            'editStudentBirthdate.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
            'editStudentAddress.required' => 'La dirección es requerida.',
            'editYear.required' => 'El ciclo escolar es requerido.',
            'editLevelId.required' => 'Seleccione el nivel educativo.',
            'editGradeId.required' => 'Seleccione el grado.',
            'editFatherFirstName.required' => 'El nombre del padre es requerido.',
            'editFatherLastName.required' => 'Los apellidos del padre son requeridos.',
            'editFatherPhone.required' => 'El teléfono del padre es requerido.',
            'editMotherFirstName.required' => 'El nombre de la madre es requerido.',
            'editMotherLastName.required' => 'Los apellidos de la madre son requeridos.',
            'editMotherPhone.required' => 'El teléfono de la madre es requerido.',
            'editGuardianType.required' => 'Seleccione quién es el encargado.',
            'editGuardianName.required' => 'El nombre del encargado es requerido.',
            'editGuardianPhone.required' => 'El teléfono del encargado es requerido.',
            'editGuardianEmail.required' => 'El correo del encargado es requerido.',
            'editGuardianEmail.email' => 'Ingrese un correo electrónico válido.',
        ];
    }

    public function updateApplication(): void
    {
        $this->authorize('admin.admissions.edit');

        $this->validate($this->editRules(), $this->editMessages());

        $this->viewing->update([
            'year' => $this->editYear,
            'level_id' => $this->editLevelId,
            'grade_id' => $this->editGradeId,
            'student_first_name' => $this->editStudentFirstName,
            'student_second_name' => $this->editStudentSecondName ?: null,
            'student_first_surname' => $this->editStudentFirstSurname,
            'student_second_surname' => $this->editStudentSecondSurname ?: null,
            'student_birthdate' => $this->editStudentBirthdate,
            'student_address' => $this->editStudentAddress,
            'student_previous_school' => $this->editStudentPreviousSchool ?: null,
            'student_religion' => $this->editStudentReligion ?: null,
            'father_first_name' => $this->editFatherEnabled ? $this->editFatherFirstName : null,
            'father_last_name' => $this->editFatherEnabled ? $this->editFatherLastName : null,
            'father_phone' => $this->editFatherEnabled ? $this->editFatherPhone : null,
            'father_workplace' => $this->editFatherEnabled ? ($this->editFatherWorkplace ?: null) : null,
            'father_nit' => $this->editFatherEnabled ? ($this->editFatherNit ?: null) : null,
            'father_profession' => $this->editFatherEnabled ? ($this->editFatherProfession ?: null) : null,
            'mother_first_name' => $this->editMotherEnabled ? $this->editMotherFirstName : null,
            'mother_last_name' => $this->editMotherEnabled ? $this->editMotherLastName : null,
            'mother_phone' => $this->editMotherEnabled ? $this->editMotherPhone : null,
            'mother_workplace' => $this->editMotherEnabled ? ($this->editMotherWorkplace ?: null) : null,
            'mother_nit' => $this->editMotherEnabled ? ($this->editMotherNit ?: null) : null,
            'mother_profession' => $this->editMotherEnabled ? ($this->editMotherProfession ?: null) : null,
            'guardian_type' => $this->editGuardianType,
            'guardian_name' => $this->editGuardianName,
            'guardian_phone' => $this->editGuardianPhone,
            'guardian_nit' => $this->editGuardianType === 'other' ? ($this->editGuardianNit ?: null) : null,
            'guardian_email' => $this->editGuardianEmail,
            'sons_count' => $this->editSonsCount !== '' ? (int) $this->editSonsCount : null,
            'sons_ages' => $this->editSonsAges ?: null,
            'daughters_count' => $this->editDaughtersCount !== '' ? (int) $this->editDaughtersCount : null,
            'daughters_ages' => $this->editDaughtersAges ?: null,
            'referral_source' => $this->editReferralSource ?: null,
            'url_documents' => $this->editUrlDocuments ?: null,
            'url_payment' => $this->editUrlPayment ?: null,
        ]);

        $this->viewing->load('level', 'grade');
        $this->viewing->refresh();

        $this->syncDocumentStatus();

        $this->viewing->load('statuses.user', 'documents');
        $this->viewing->refresh();

        $this->dispatch('showAlert', ['title' => 'Información actualizada correctamente.', 'type' => 'success']);
        unset($this->applications, $this->pendingCount);
    }

    // ── Acciones de estado ───────────────────────────────────────
    public function markEmailed(int $id): void
    {
        $this->applyStatus($id, 'emailed');
    }

    public function markAccepted(int $id): void
    {
        $this->applyStatus($id, 'accepted');
    }

    public function openReject(int $id): void
    {
        $this->authorize('admin.admissions.manage');
        $this->rejectingId = $id;
        $this->rejectionNotes = '';
        $this->dispatch('openRejectModal');
    }

    public function confirmReject(): void
    {
        $this->authorize('admin.admissions.manage');

        if (! $this->rejectingId) {
            return;
        }

        $this->applyStatus($this->rejectingId, 'rejected', $this->rejectionNotes);
        $this->rejectingId = null;
        $this->rejectionNotes = '';
        $this->dispatch('closeRejectModal');
    }

    public function resetToPending(int $id): void
    {
        $this->authorize('admin.admissions.manage');

        $app = AdmissionApplication::findOrFail($id);
        if (in_array($app->current_status, ['reviewed', 'billed', 'psychometric', 'accepted'])) {
            return;
        }

        $this->applyStatus($id, 'pending');
    }

    public function toggleDocument(string $field): void
    {
        $this->authorize('admin.admissions.manage');

        if (! array_key_exists($field, AdmissionApplicationDocument::fields())) {
            return;
        }

        if ($this->viewing->current_status === 'pending') {
            return;
        }

        $doc = $this->viewing->documents;
        $doc->update([$field => ! $doc->$field]);
        $doc->refresh();

        $this->syncDocumentStatus();

        $this->viewing->load('statuses.user', 'documents');
        $this->viewing->refresh();
        unset($this->applications, $this->pendingCount);

        $this->dispatch('toastMessage', ['message' => 'Documento actualizado.', 'type' => 'success']);
    }

    private function syncDocumentStatus(): void
    {
        if (! in_array($this->viewing->current_status, ['emailed', 'reviewed'])) {
            return;
        }

        $doc = $this->viewing->documents;
        if (! $doc) {
            return;
        }

        $urlsComplete = ! empty($this->viewing->url_documents) && ! empty($this->viewing->url_payment);
        $isComplete = $doc->isComplete() && $urlsComplete;
        $isReviewed = $this->viewing->current_status === 'reviewed';

        if ($isComplete && ! $isReviewed) {
            $doc->update(['completed_at' => now()]);
            $this->viewing->update(['current_status' => 'reviewed']);
            $this->viewing->statuses()->create([
                'status' => 'reviewed',
                'notes' => 'Documentación completa recibida.',
                'user_id' => Auth::id(),
            ]);
        } elseif (! $isComplete && $isReviewed) {
            $doc->update(['completed_at' => null]);
            $this->viewing->update(['current_status' => 'emailed']);
            $this->viewing->statuses()->create([
                'status' => 'emailed',
                'notes' => 'Documentación incompleta.',
                'user_id' => Auth::id(),
            ]);
        }
    }

    private function applyStatus(int $id, string $status, ?string $notes = null): void
    {
        $this->authorize('admin.admissions.manage');

        $app = AdmissionApplication::findOrFail($id);
        $app->update(['current_status' => $status]);
        $app->statuses()->create([
            'status' => $status,
            'notes' => $notes ?: null,
            'user_id' => Auth::id(),
        ]);

        if ($this->viewing?->id === $id) {
            $this->viewing->load('statuses.user', 'documents');
            $this->viewing->refresh();
        }

        $this->dispatch('toastMessage', ['message' => 'Estado actualizado.', 'type' => 'success']);
        unset($this->applications, $this->pendingCount);
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.admin.students.admission-list');
    }
}
