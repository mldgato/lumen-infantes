<?php

namespace App\Livewire\Students;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;

class StudentList extends Component
{
    use WithPagination;

    // Le indicamos a Livewire que use los estilos de Bootstrap para la paginación
    protected $paginationTheme = 'bootstrap';

    public $search = '';

    // Este hook reinicia la paginación a la página 1 cuando el usuario busca algo
    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        // Filtramos usando el scope 'role' que provee Spatie
        $students = User::role('Estudiante')
            ->where(function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('cui', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->orderBy('surname', 'asc') // Ordenamos por apellido
            ->paginate(10);

        return view('livewire.students.student-list', compact('students'));
    }
}
