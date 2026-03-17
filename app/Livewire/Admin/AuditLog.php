<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog as AuditLogModel;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class AuditLog extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public bool   $readyToLoad    = false;
    public string $filterModule   = '';
    public string $filterEvent    = '';
    public string $filterUser     = '';
    public string $filterDateFrom = '';
    public string $filterDateTo   = '';
    public string $search         = '';
    public string $cant           = '25';
    public ?int   $selectedLogId  = null;

    protected $queryString = [
        'filterModule'   => ['except' => ''],
        'filterEvent'    => ['except' => ''],
        'filterUser'     => ['except' => ''],
        'filterDateFrom' => ['except' => ''],
        'filterDateTo'   => ['except' => ''],
        'search'         => ['except' => ''],
        'cant'           => ['except' => '25'],
    ];

    public function loadData(): void
    {
        $this->readyToLoad = true;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }
    public function updatingFilterModule(): void
    {
        $this->resetPage();
    }
    public function updatingFilterEvent(): void
    {
        $this->resetPage();
    }
    public function updatingFilterUser(): void
    {
        $this->resetPage();
    }
    public function updatingFilterDateFrom(): void
    {
        $this->resetPage();
    }
    public function updatingFilterDateTo(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->filterModule = $this->filterEvent = $this->filterUser = '';
        $this->filterDateFrom = $this->filterDateTo = $this->search = '';
        $this->resetPage();
    }

    public function viewDetail(int $id): void
    {
        $this->selectedLogId = $id;
        $this->dispatch('openAuditDetailModal');
    }

    public static function moduleBadge(string $module): string
    {
        return match ($module) {
            'Cuadros'         => 'badge-primary',
            'Calificaciones'  => 'badge-info',
            'Inscripciones'   => 'badge-success',
            'Usuarios'        => 'badge-warning',
            'Cambio de Notas' => 'badge-danger',
            'Configuración'   => 'badge-dark',
            default           => 'badge-secondary',
        };
    }

    public static function eventBadge(string $event): string
    {
        return match ($event) {
            'created'                    => 'badge-success',
            'updated'                    => 'badge-warning',
            'deleted'                    => 'badge-danger',
            'approved'                   => 'badge-success',
            'rejected'                   => 'badge-danger',
            'status_changed'             => 'badge-info',
            'score_updated'              => 'badge-warning',
            'enrolled'                   => 'badge-primary',
            'enrollment_status_changed'  => 'badge-info',
            'config_changed'             => 'badge-dark',
            default                      => 'badge-secondary',
        };
    }

    public function render()
    {
        $modules = AuditLogModel::select('module')->distinct()->orderBy('module')->pluck('module');
        $events  = AuditLogModel::select('event')->distinct()->orderBy('event')->pluck('event');
        $users   = User::whereHas('auditLogs')->orderBy('name')->get(['id', 'name']);

        $selectedLog = $this->selectedLogId
            ? AuditLogModel::with('user')->find($this->selectedLogId)
            : null;

        $logs = [];

        if ($this->readyToLoad) {
            $logs = AuditLogModel::with('user')
                ->when($this->filterModule,   fn($q) => $q->where('module', $this->filterModule))
                ->when($this->filterEvent,    fn($q) => $q->where('event', $this->filterEvent))
                ->when($this->filterUser,     fn($q) => $q->where('user_id', $this->filterUser))
                ->when($this->filterDateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->filterDateFrom))
                ->when($this->filterDateTo,   fn($q) => $q->whereDate('created_at', '<=', $this->filterDateTo))
                ->when($this->search,         fn($q) => $q->where('description', 'like', '%' . $this->search . '%'))
                ->latest()
                ->paginate($this->cant);
        }

        return view('livewire.admin.audit-log', compact('logs', 'modules', 'events', 'users', 'selectedLog'));
    }
}
