<?php

namespace App\Livewire\Admin\Roles;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class ShowRoles extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $name             = '';
    public ?int $role_id            = null;
    public array $selectedPermissions = [];
    public string $search           = '';
    public string $permissionSearch = '';
    public string $permissionSearchCreate = '';
    public string $sort             = 'name';
    public string $direction        = 'asc';
    public string $cant             = '10';
    public bool $readyToLoad        = false;

    protected $queryString = [
        'cant'      => ['except' => '10'],
        'sort'      => ['except' => 'name'],
        'direction' => ['except' => 'asc'],
        'search'    => ['except' => ''],
    ];

    protected $listeners = ['delete'];

    public function loadRoles(): void
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
        $this->reset(['name', 'selectedPermissions', 'role_id', 'permissionSearch', 'permissionSearchCreate']);
        $this->resetValidation();
    }

    public function save(): void
    {
        $this->authorize('admin.roles.index');

        $this->validate([
            'name' => 'required|string|max:255|unique:roles,name',
        ], [
            'name.required' => 'El nombre del rol es obligatorio.',
            'name.unique'   => 'Ya existe un rol con ese nombre.',
        ]);

        $role = Role::create(['name' => $this->name, 'guard_name' => 'web']);

        if (!empty($this->selectedPermissions)) {
            $role->permissions()->sync($this->selectedPermissions);
        }

        $this->resetFields();

        $this->dispatch('closeModalMessaje', [
            'title'   => '¡Éxito!',
            'message' => 'Rol creado exitosamente.',
            'type'    => 'success',
            'modalId' => 'CreateRoleModal',
        ]);
    }

    public function edit(int $id): void
    {
        $role                    = Role::with('permissions')->findOrFail($id);
        $this->role_id           = $id;
        $this->name              = $role->name;
        $this->selectedPermissions = $role->permissions->pluck('id')->toArray();
        $this->permissionSearch  = '';
        $this->resetValidation();
    }

    public function update(): void
    {
        $this->authorize('admin.roles.index');

        $this->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $this->role_id,
        ], [
            'name.required' => 'El nombre del rol es obligatorio.',
            'name.unique'   => 'Ya existe un rol con ese nombre.',
        ]);

        $role = Role::findOrFail($this->role_id);
        $role->update(['name' => $this->name]);
        $role->permissions()->sync($this->selectedPermissions ?? []);

        $this->resetFields();

        $this->dispatch('closeModalMessaje', [
            'title'   => '¡Actualizado!',
            'message' => 'Rol actualizado exitosamente.',
            'type'    => 'success',
            'modalId' => 'EditRoleModal',
        ]);
    }

    public function confirmDelete(int $id): void
    {
        $this->dispatch('confirmDeleteRole', ['id' => $id]);
    }

    public function delete(int $id): void
    {
        $this->authorize('admin.roles.index');

        $role = Role::findOrFail($id);

        if ($role->users()->count() > 0) {
            $this->dispatch('showAlert', [
                'title'   => 'Error',
                'message' => 'No se puede eliminar el rol porque tiene usuarios asignados.',
                'type'    => 'error',
            ]);
            return;
        }

        $role->delete();

        $this->dispatch('showAlert', [
            'title'   => '¡Eliminado!',
            'message' => 'Rol eliminado exitosamente.',
            'type'    => 'success',
        ]);
    }

    public function clearPermissionSearch(): void
    {
        $this->permissionSearch = '';
    }
    public function clearPermissionSearchCreate(): void
    {
        $this->permissionSearchCreate = '';
    }

    public function render()
    {
        $allPermissions = Permission::orderBy('description')->get();

        $filteredPermissions = $this->permissionSearch
            ? $allPermissions->filter(fn($p) =>
            stripos($p->description, $this->permissionSearch) !== false ||
                stripos($p->name, $this->permissionSearch) !== false)
            : $allPermissions;

        $filteredPermissionsCreate = $this->permissionSearchCreate
            ? $allPermissions->filter(fn($p) =>
            stripos($p->description, $this->permissionSearchCreate) !== false ||
                stripos($p->name, $this->permissionSearchCreate) !== false)
            : $allPermissions;

        $roles = $this->readyToLoad
            ? Role::with('permissions')
            ->where('name', 'like', '%' . $this->search . '%')
            ->orderBy($this->sort, $this->direction)
            ->paginate($this->cant)
            : [];

        return view('livewire.admin.roles.show-roles', compact(
            'roles',
            'filteredPermissions',
            'filteredPermissionsCreate',
        ));
    }
}
