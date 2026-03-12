<?php

namespace App\Livewire\Admin;

use App\Livewire\Forms\AcademicConfigurationForm;
use App\Models\AcademicConfiguration;
use App\Models\AcademicConfigurationActivity;
use App\Models\ActivityType;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;

class AcademicConfigurations extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public AcademicConfigurationForm $form;

    public string $search    = '';
    public string $sort      = 'year';
    public string $direction = 'desc';
    public string $cant      = '10';
    public bool $readyToLoad = false;

    // Gestión de actividades
    public ?int $managingConfigurationId       = null;
    public ?AcademicConfiguration $managingConfiguration = null;

    // Formulario de actividad
    public bool $showActivityForm        = false;
    public ?int $editingActivityId       = null;
    public int|string $activity_type_id  = '';
    public int|string $quantity          = '';
    public int|string $points_each       = '';

    // Copiar configuración
    public ?int $copyingConfigurationId  = null;
    public string $copy_year             = '';

    protected $queryString = [
        'cant'      => ['except' => '10'],
        'sort'      => ['except' => 'year'],
        'direction' => ['except' => 'desc'],
        'search'    => ['except' => ''],
    ];

    public function loadConfigurations(): void
    {
        $this->readyToLoad = true;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCant(): void
    {
        $this->resetPage();
    }

    public function order(string $sort): void
    {
        if ($this->sort === $sort) {
            $this->direction = $this->direction === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort      = $sort;
            $this->direction = 'asc';
        }
    }

    public function resetFields(): void
    {
        $this->form->resetForm();
        $this->resetValidation();
    }

    public function edit(int $id): void
    {
        $this->resetFields();
        $this->form->setConfiguration(AcademicConfiguration::findOrFail($id));
    }

    public function save(): void
    {
        if ($this->form->configuration) {
            $this->authorize('admin.academic-configurations.edit');
            $this->form->update();
            $mensaje = 'Configuración actualizada exitosamente.';
        } else {
            $this->authorize('admin.academic-configurations.create');
            $this->form->store();
            $mensaje = 'Configuración creada exitosamente.';
        }

        $this->resetFields();

        $this->dispatch('closeModalMessaje', [
            'title'   => '¡Éxito!',
            'message' => $mensaje,
            'type'    => 'success',
            'modalId' => 'ConfigurationModal',
        ]);
    }

    public function delete(int $id): void
    {
        $this->authorize('admin.academic-configurations.delete');
        AcademicConfiguration::findOrFail($id)->delete();

        $this->dispatch('showAlert', [
            'title'   => '¡Eliminado!',
            'message' => 'Configuración eliminada exitosamente.',
            'type'    => 'success',
        ]);
    }

    // ==========================================
    // GESTIÓN DE ACTIVIDADES
    // ==========================================

    public function manageActivities(int $id): void
    {
        $this->managingConfigurationId = $id;
        $this->managingConfiguration   = AcademicConfiguration::with([
            'activities.activityType',
        ])->findOrFail($id);
        $this->resetActivityForm();
    }

    public function resetActivityForm(): void
    {
        $this->showActivityForm   = false;
        $this->editingActivityId  = null;
        $this->activity_type_id   = '';
        $this->quantity           = '';
        $this->points_each        = '';
        $this->resetValidation();
    }

    public function openActivityForm(): void
    {
        $this->resetActivityForm();
        $this->showActivityForm = true;
    }

    public function editActivity(int $id): void
    {
        $activity = AcademicConfigurationActivity::findOrFail($id);

        $this->editingActivityId = $activity->id;
        $this->activity_type_id  = $activity->activity_type_id;
        $this->quantity          = $activity->quantity;
        $this->points_each       = $activity->points_each;
        $this->showActivityForm  = true;
    }

    public function saveActivity(): void
    {
        $this->validate([
            'activity_type_id' => 'required|exists:activity_types,id',
            'quantity'         => 'required|integer|min:1',
            'points_each'      => 'required|numeric|min:0.01',
        ], [
            'activity_type_id.required' => 'El tipo de actividad es obligatorio.',
            'activity_type_id.exists'   => 'El tipo de actividad no es válido.',
            'quantity.required'         => 'La cantidad es obligatoria.',
            'quantity.min'              => 'La cantidad debe ser al menos 1.',
            'points_each.required'      => 'Los puntos son obligatorios.',
            'points_each.min'           => 'Los puntos deben ser mayor a 0.',
        ]);

        if ($this->editingActivityId) {
            AcademicConfigurationActivity::findOrFail($this->editingActivityId)->update([
                'activity_type_id' => $this->activity_type_id,
                'quantity'         => $this->quantity,
                'points_each'      => $this->points_each,
            ]);
        } else {
            AcademicConfigurationActivity::create([
                'academic_configuration_id' => $this->managingConfigurationId,
                'activity_type_id'          => $this->activity_type_id,
                'quantity'                  => $this->quantity,
                'points_each'               => $this->points_each,
            ]);
        }

        $this->managingConfiguration = AcademicConfiguration::with([
            'activities.activityType',
        ])->findOrFail($this->managingConfigurationId);

        $this->resetActivityForm();

        $this->dispatch('toastMessage', [
            'type'    => 'success',
            'message' => $this->editingActivityId ? 'Actividad actualizada.' : 'Actividad agregada.',
        ]);
    }

    public function deleteActivity(int $id): void
    {
        AcademicConfigurationActivity::findOrFail($id)->delete();

        $this->managingConfiguration = AcademicConfiguration::with([
            'activities.activityType',
        ])->findOrFail($this->managingConfigurationId);

        $this->dispatch('toastMessage', [
            'type'    => 'info',
            'message' => 'Actividad eliminada.',
        ]);
    }

    // ==========================================
    // COPIAR CONFIGURACIÓN
    // ==========================================

    public function openCopyModal(int $id): void
    {
        $this->copyingConfigurationId = $id;
        $this->copy_year              = '';
        $this->resetValidation();
    }

    public function copyConfiguration(): void
    {
        $this->validate([
            'copy_year' => [
                'required',
                'digits:4',
                'integer',
                Rule::unique('academic_configurations', 'year'),
            ],
        ], [
            'copy_year.required' => 'El año destino es obligatorio.',
            'copy_year.digits'   => 'El año debe tener exactamente 4 dígitos.',
            'copy_year.unique'   => 'Ya existe una configuración para ese año.',
        ]);

        $original = AcademicConfiguration::with('activities')->findOrFail($this->copyingConfigurationId);

        $nueva = AcademicConfiguration::create([
            'year' => $this->copy_year,
            'mode' => $original->mode,
        ]);

        foreach ($original->activities as $activity) {
            AcademicConfigurationActivity::create([
                'academic_configuration_id' => $nueva->id,
                'activity_type_id'          => $activity->activity_type_id,
                'quantity'                  => $activity->quantity,
                'points_each'               => $activity->points_each,
            ]);
        }

        $this->copyingConfigurationId = null;
        $this->copy_year              = '';

        $this->dispatch('closeModalMessaje', [
            'title'   => '¡Copiado!',
            'message' => 'Configuración copiada exitosamente.',
            'type'    => 'success',
            'modalId' => 'CopyConfigurationModal',
        ]);
    }

    public function render()
    {
        $configurations = $this->readyToLoad
            ? AcademicConfiguration::withCount('activities')
            ->where('year', 'like', '%' . $this->search . '%')
            ->orderBy($this->sort, $this->direction)
            ->paginate($this->cant)
            : [];

        $activityTypes = ActivityType::orderBy('name')->get();

        return view('livewire.admin.academic-configurations', compact('configurations', 'activityTypes'));
    }
}
