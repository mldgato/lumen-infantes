<?php

namespace App\Livewire\Users;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Livewire\Forms\UserForm;
use App\Livewire\Forms\MedicalForm;
use App\Livewire\Forms\ProfessorForm;

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
        $this->activeTab = 'general'; // Siempre vuelve a la pestaña 1
        $this->resetValidation();
    }

    public function edit($id)
    {
        $this->resetFields();
        $user = User::with(['medicalRecord', 'professor', 'roles'])->findOrFail($id);

        $this->userForm->setUser($user);
        $this->medicalForm->setMedicalRecord($user->medicalRecord);
        $this->professorForm->setProfessor($user->professor);
        $this->selected_roles = $user->roles->pluck('name')->toArray();
    }

    public function save()
    {
        $this->validate([
            'selected_roles' => 'required|array|min:1',
        ], [
            'selected_roles.required' => 'Debe asignar al menos un rol al usuario.',
        ]);

        if ($this->userForm->user) {
            $user = $this->userForm->update();
            $mensaje = 'Usuario actualizado exitosamente.';
        } else {
            $user = $this->userForm->store();
            $mensaje = 'Usuario creado exitosamente.';
        }

        $user->syncRoles($this->selected_roles);
        $this->medicalForm->save($user->id);

        if (in_array('Profesor', $this->selected_roles)) {
            $this->professorForm->save($user->id);
        }

        $this->resetFields();

        // En Livewire 3 es mejor enviar un array asociativo al JS
        $this->dispatch('closeModalMessaje', [
            'title' => '¡Éxito!',
            'message' => $mensaje,
            'type' => 'success',
            'modalId' => 'UserModal'
        ]);
    }

    public function render()
    {
        $roles = Role::where('name', '!=', 'Estudiante')->get();

        if ($this->readyToLoad) {
            $users = User::whereDoesntHave('roles', function ($query) {
                $query->where('name', 'Estudiante');
            })
                ->where(function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('cui', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                })
                ->orderBy($this->sort, $this->direction)
                ->paginate($this->cant);
        } else {
            $users = [];
        }

        return view('livewire.users.user-list', compact('users', 'roles'));
    }
}
