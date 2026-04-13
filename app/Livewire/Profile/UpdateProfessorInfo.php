<?php

namespace App\Livewire\Profile;

use App\Services\AuditService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class UpdateProfessorInfo extends Component
{
    public $hire_date;

    public $nit;

    public $teaching_cedula;

    public $igss_affiliation;

    public $title;

    public $bachelor_degree;

    public $spouse_name;

    public $spouse_phone;

    public function mount(): void
    {
        if (! Auth::user()->hasRole('Profesor')) {
            abort(403, 'No tienes permiso para acceder a la información de docente.');
        }

        $professor = Auth::user()->professor;

        if ($professor) {
            $this->hire_date = $professor->hire_date?->format('Y-m-d');
            $this->nit = $professor->nit;
            $this->teaching_cedula = $professor->teaching_cedula;
            $this->igss_affiliation = $professor->igss_affiliation;
            $this->title = $professor->title;
            $this->bachelor_degree = $professor->bachelor_degree;
            $this->spouse_name = $professor->spouse_name;
            $this->spouse_phone = $professor->spouse_phone;
        }
    }

    public function updateProfessor(): void
    {
        $this->validate([
            'hire_date' => ['required', 'date'],
            'nit' => ['nullable', 'string', 'max:255'],
            'teaching_cedula' => ['nullable', 'string', 'max:255'],
            'igss_affiliation' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'bachelor_degree' => ['nullable', 'string', 'max:255'],
            'spouse_name' => ['nullable', 'string', 'max:255'],
            'spouse_phone' => ['nullable', 'string', 'max:255'],
        ]);

        $professor = Auth::user()->professor;

        $newData = [
            'hire_date' => $this->hire_date,
            'nit' => $this->nit,
            'teaching_cedula' => $this->teaching_cedula,
            'igss_affiliation' => $this->igss_affiliation,
            'title' => $this->title,
            'bachelor_degree' => $this->bachelor_degree,
            'spouse_name' => $this->spouse_name,
            'spouse_phone' => $this->spouse_phone,
        ];

        $changed = [];

        if ($professor) {
            $oldData = [
                'hire_date' => $professor->hire_date?->format('Y-m-d'),
                'nit' => $professor->nit,
                'teaching_cedula' => $professor->teaching_cedula,
                'igss_affiliation' => $professor->igss_affiliation,
                'title' => $professor->title,
                'bachelor_degree' => $professor->bachelor_degree,
                'spouse_name' => $professor->spouse_name,
                'spouse_phone' => $professor->spouse_phone,
            ];

            foreach ($newData as $field => $newValue) {
                if ($oldData[$field] !== $newValue) {
                    $changed[$field] = ['old' => $oldData[$field], 'new' => $newValue];
                }
            }
        }

        Auth::user()->professor()->updateOrCreate(
            ['user_id' => Auth::id()],
            $newData,
        );

        if (! empty($changed)) {
            AuditService::professorProfileUpdated(Auth::user(), $changed);
        }

        session()->flash('profile_message', 'Información profesional actualizada.');
        $this->redirect(route('profile'));
    }

    public function render()
    {
        return view('livewire.profile.update-professor-info');
    }
}
