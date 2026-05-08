<?php

namespace App\Livewire\Users;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Livewire\Forms\UserForm;
use App\Livewire\Forms\MedicalForm;
use App\Livewire\Forms\ProfessorForm;
use App\Models\Level;
use App\Services\AuditService;

class UserList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public UserForm $userForm;
    public MedicalForm $medicalForm;
    public ProfessorForm $professorForm;

    public $search = '';
    public $sort = 'name';
    public $direction = 'asc';
    public $cant = '10';
    public $readyToLoad = false;

    // Control de pestañas de UI
    public $activeTab = 'general';
    public $selected_roles = [];
    public array $selected_levels = [];

    protected $queryString = [
        'cant' => ['except' => '10'],
        'sort' => ['except' => 'name'],
        'direction' => ['except' => 'asc'],
        'search' => ['except' => '']
    ];

    public function loadUsers()
    {
        $this->readyToLoad = true;
    }
    public function updatingSearch()
    {
        $this->resetPage();
    }
    public function updatingCant()
    {
        $this->resetPage();
    }

    public function order($sort)
    {
        if ($this->sort == $sort) {
            $this->direction = $this->direction == 'desc' ? 'asc' : 'desc';
        } else {
            $this->sort = $sort;
            $this->direction = 'asc';
        }
    }

    public function resetFields()
    {
        $this->userForm->resetForm();
        $this->medicalForm->resetForm();
        $this->professorForm->resetForm();
        $this->selected_roles = [];
        $this->selected_levels = [];
        $this->activeTab = 'general';
        $this->resetValidation();
    }

    public function edit($id)
    {
        $this->resetFields();
        $user = User::with(['medicalRecord', 'professor', 'roles', 'levels'])->findOrFail($id);

        $this->userForm->setUser($user);
        $this->medicalForm->setMedicalRecord($user->medicalRecord);
        $this->professorForm->setProfessor($user->professor);
        $this->selected_roles = $user->roles->pluck('name')->toArray();
        $this->selected_levels = $user->levels->pluck('id')->toArray();
    }

    public function save()
    {
        $this->validate([
            'selected_roles' => 'required|array|min:1',
        ], [
            'selected_roles.required' => 'Debe asignar al menos un rol al usuario.',
        ]);

        if ($this->userForm->user) {
            $existingUser = User::findOrFail($this->userForm->user->id);
            $oldValues    = $existingUser->only([
                'first_name',
                'middle_name',
                'surname',
                'second_surname',
                'email',
                'cellphone',
                'address',
                'is_active',
            ]);

            $user = $this->userForm->update();

            $newValues = $user->only([
                'first_name',
                'middle_name',
                'surname',
                'second_surname',
                'email',
                'cellphone',
                'address',
                'is_active',
            ]);

            $changed = array_filter(
                array_map(
                    fn($key) => $oldValues[$key] != $newValues[$key]
                        ? ['old' => $oldValues[$key], 'new' => $newValues[$key]]
                        : null,
                    array_keys($oldValues)
                )
            );

            if (count($changed) > 0) {
                AuditService::userUpdated($user, $changed);
            }

            $mensaje = 'Usuario actualizado exitosamente.';
        } else {
            $user = $this->userForm->store();
            AuditService::userCreated($user);
            $mensaje = 'Usuario creado exitosamente.';
        }

        $user->syncRoles($this->selected_roles);
        $user->levels()->sync($this->selected_levels);
        $this->medicalForm->save($user->id);

        if (in_array('Profesor', $this->selected_roles)) {
            $this->professorForm->save($user->id);
        }

        $this->resetFields();

        $this->dispatch('closeModalMessaje', [
            'title'   => '¡Éxito!',
            'message' => $mensaje,
            'type'    => 'success',
            'modalId' => 'UserModal',
        ]);
    }

    public function render()
    {
        $isSuperAdmin = auth()->user()->hasRole('Super Administrador');

        $roles = Role::where('name', '!=', 'Estudiante')
            ->when(! $isSuperAdmin, fn($q) => $q->where('name', '!=', 'Super Administrador'))
            ->get();

        if ($this->readyToLoad) {
            $users = User::whereDoesntHave(
                'roles',
                fn($q) =>
                $q->where('name', 'Estudiante')
            )
                ->when(
                    ! $isSuperAdmin,
                    fn($q) =>
                    $q->whereDoesntHave(
                        'roles',
                        fn($q) =>
                        $q->where('name', 'Super Administrador')
                    )
                )
                ->where(
                    fn($query) =>
                    $query->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('cui', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%')
                )
                ->orderBy($this->sort, $this->direction)
                ->paginate($this->cant);
        } else {
            $users = [];
        }

        $levels = Level::orderBy('ordering')->get();

        return view('livewire.users.user-list', compact('users', 'roles', 'levels'));
    }
}
