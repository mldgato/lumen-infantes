<?php

namespace App\Livewire\Admin\Students;

use App\Models\AdmissionApplication;
use App\Models\AdmissionBilling;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class AdmissionBillingList extends Component
{
    use WithPagination;

    // ── Filtros ──────────────────────────────────────────────────
    public string $search = '';

    public string $filterYear = '';

    public string $filterStatus = '';

    public int $cant = 15;

    protected $queryString = ['search', 'filterYear', 'filterStatus', 'cant'];

    // ── Modal ────────────────────────────────────────────────────
    public ?AdmissionApplication $viewing = null;

    public string $invoiceNumber = '';

    public string $invoiceDate = '';

    // ── Mount ────────────────────────────────────────────────────
    public function mount(): void
    {
        $this->authorize('admin.admissions.billing');
        $this->filterYear = (string) now()->year;
    }

    // ── Reseteo de paginación ─────────────────────────────────────
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

    public function updatingCant(): void
    {
        $this->resetPage();
    }

    // ── Computed ──────────────────────────────────────────────────
    #[Computed]
    public function applications()
    {
        return AdmissionApplication::with(['level', 'grade', 'billing'])
            ->when($this->filterYear, fn ($q) => $q->where('year', $this->filterYear))
            ->when($this->filterStatus, fn ($q) => $q->where('current_status', $this->filterStatus))
            ->when($this->search, function ($q) {
                $q->where(function ($inner) {
                    $inner->where('student_first_name', 'like', "%{$this->search}%")
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

    // ── Abrir modal ───────────────────────────────────────────────
    public function openModal(int $id): void
    {
        $this->authorize('admin.admissions.billing');

        $this->viewing = AdmissionApplication::with(['level', 'grade', 'billing.user'])
            ->findOrFail($id);

        $this->invoiceNumber = '';
        $this->invoiceDate = '';
        $this->resetValidation();

        $this->dispatch('openBillingModal');
    }

    // ── Guardar factura ───────────────────────────────────────────
    public function saveBilling(): void
    {
        $this->authorize('admin.admissions.billing');

        if ($this->viewing->billing) {
            return;
        }

        $this->validate([
            'invoiceNumber' => ['required', 'string', 'max:100'],
            'invoiceDate' => ['required', 'date'],
        ], [
            'invoiceNumber.required' => 'El número de factura es requerido.',
            'invoiceDate.required' => 'La fecha de la factura es requerida.',
            'invoiceDate.date' => 'Ingrese una fecha válida.',
        ]);

        AdmissionBilling::create([
            'admission_application_id' => $this->viewing->id,
            'invoice_number' => trim($this->invoiceNumber),
            'invoice_date' => $this->invoiceDate,
            'user_id' => Auth::id(),
        ]);

        $this->viewing->load('billing.user');
        $this->viewing->refresh();
        unset($this->applications);

        $this->dispatch('showAlert', ['title' => 'Factura registrada correctamente.', 'type' => 'success']);
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.admin.students.admission-billing-list');
    }
}
