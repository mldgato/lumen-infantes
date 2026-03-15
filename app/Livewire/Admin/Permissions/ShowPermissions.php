<?php

namespace App\Livewire\Admin\Permissions;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ShowPermissions extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $name        = '';
    public string $description = '';
    public ?int $permission_id = null;
    public array $selectedRoles = [];
    public string $search      = '';
    public string $roleSearch  = '';
    public string $roleSearchCreate = '';
    public string $sort        = 'description';
    public string $direction   = 'asc';
    public string $cant        = '10';
    public bool $readyToLoad   = false;

    protected $queryString = [
        'cant'      => ['except' => '10'],
        'sort'      => ['except' => 'description'],
        'direction' => ['except' => 'asc'],
        'search'    => ['except' => ''],
    ];

    protected $listeners = ['delete'];

    public function loadPermissions(): void
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
        $this->reset(['name', 'description', 'selectedRoles', 'permission_id', 'roleSearch', 'roleSearchCreate']);
        $this->resetValidation();
    }

    public function save(): void
    {
        $this->authorize('admin.permissions.index');

        $this->validate([
            'name'        => 'required|string|max:255|unique:permissions,name',
            'description' => 'required|string|max:255',
        ], [
            'name.required'        => 'El nombre del permiso es obligatorio.',
            'name.unique'          => 'Ya existe un permiso con ese nombre.',
            'description.required' => 'La descripción es obligatoria.',
        ]);

        $permission = Permission::create([
            'name'        => $this->name,
            'description' => $this->description,
            'guard_name'  => 'web',
        ]);

        if (!empty($this->selectedRoles)) {
            $roles = Role::whereIn('id', $this->selectedRoles)->get();
            $permission->syncRoles($roles);
        }

        $this->resetFields();

        $this->dispatch('closeModalMessaje', [
            'title'   => '¡Éxito!',
            'message' => 'Permiso creado exitosamente.',
            'type'    => 'success',
            'modalId' => 'CreatePermissionModal',
        ]);
    }

    public function edit(int $id): void
    {
        $permission            = Permission::with('roles')->findOrFail($id);
        $this->permission_id   = $id;
        $this->name            = $permission->name;
        $this->description     = $permission->description;
        $this->selectedRoles   = $permission->roles->pluck('id')->toArray();
        $this->roleSearch      = '';
        $this->resetValidation();
    }

    public function update(): void
    {
        $this->authorize('admin.permissions.index');

        $this->validate([
            'name'        => 'required|string|max:255|unique:permissions,name,' . $this->permission_id,
            'description' => 'required|string|max:255',
        ], [
            'name.required'        => 'El nombre del permiso es obligatorio.',
            'name.unique'          => 'Ya existe un permiso con ese nombre.',
            'description.required' => 'La descripción es obligatoria.',
        ]);

        $permission = Permission::findOrFail($this->permission_id);
        $permission->update(['name' => $this->name, 'description' => $this->description]);

        if (!empty($this->selectedRoles)) {
            $roles = Role::whereIn('id', $this->selectedRoles)->get();
            $permission->syncRoles($roles);
        } else {
            $permission->roles()->detach();
        }

        $this->resetFields();

        $this->dispatch('closeModalMessaje', [
            'title'   => '¡Actualizado!',
            'message' => 'Permiso actualizado exitosamente.',
            'type'    => 'success',
            'modalId' => 'EditPermissionModal',
        ]);
    }

    public function confirmDelete(int $id): void
    {
        $this->dispatch('confirmDeletePermission', ['id' => $id]);
    }

    public function delete(int $id): void
    {
        $this->authorize('admin.permissions.index');

        $permission = Permission::findOrFail($id);

        if ($permission->roles()->count() > 0) {
            $this->dispatch('showAlert', [
                'title'   => 'Error',
                'message' => 'No se puede eliminar el permiso porque está asignado a roles.',
                'type'    => 'error',
            ]);
            return;
        }

        $permission->delete();

        $this->dispatch('showAlert', [
            'title'   => '¡Eliminado!',
            'message' => 'Permiso eliminado exitosamente.',
            'type'    => 'success',
        ]);
    }

    public function clearRoleSearch(): void
    {
        $this->roleSearch = '';
    }
    public function clearRoleSearchCreate(): void
    {
        $this->roleSearchCreate = '';
    }

    public function render()
    {
        $allRoles = Role::orderBy('name')->get();

        $filteredRoles = $this->roleSearch
            ? $allRoles->filter(fn($r) => stripos($r->name, $this->roleSearch) !== false)
            : $allRoles;

        $filteredRolesCreate = $this->roleSearchCreate
            ? $allRoles->filter(fn($r) => stripos($r->name, $this->roleSearchCreate) !== false)
            : $allRoles;

        $permissions = $this->readyToLoad
            ? Permission::with('roles')
            ->where(
                fn($q) => $q
                    ->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%')
            )
            ->orderBy($this->sort, $this->direction)
            ->paginate($this->cant)
            : [];

        return view('livewire.admin.permissions.show-permissions', compact(
            'permissions',
            'filteredRoles',
            'filteredRolesCreate',
        ));
    }
}
