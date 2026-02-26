<?php

namespace App\Livewire\Forms;

use Livewire\Form;
use App\Models\Professor;

class ProfessorForm extends Form
{
    public ?Professor $professor = null;

    public $hire_date = '';
    public $nit = '';
    public $teaching_cedula = '';
    public $igss_affiliation = '';
    public $title = '';
    public $bachelor_degree = '';
    public $spouse_name = '';
    public $spouse_phone = '';

    public function rules()
    {
        return [
            'hire_date' => 'required|date',
            'nit' => 'nullable|string|max:255',
            'teaching_cedula' => 'nullable|string|max:255',
            'igss_affiliation' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'bachelor_degree' => 'nullable|string|max:255',
            'spouse_name' => 'nullable|string|max:255',
            'spouse_phone' => 'nullable|string|max:255',
        ];
    }

    public function setProfessor(?Professor $professor)
    {
        if ($professor) {
            $this->professor = $professor;
            $this->hire_date = $professor->hire_date ? $professor->hire_date->format('Y-m-d') : '';
            $this->nit = $professor->nit;
            $this->teaching_cedula = $professor->teaching_cedula;
            $this->igss_affiliation = $professor->igss_affiliation;
            $this->title = $professor->title;
            $this->bachelor_degree = $professor->bachelor_degree;
            $this->spouse_name = $professor->spouse_name;
            $this->spouse_phone = $professor->spouse_phone;
        } else {
            $this->resetForm();
        }
    }

    public function save($userId)
    {
        $this->validate();

        Professor::updateOrCreate(
            ['user_id' => $userId],
            $this->only([
                'hire_date',
                'nit',
                'teaching_cedula',
                'igss_affiliation',
                'title',
                'bachelor_degree',
                'spouse_name',
                'spouse_phone'
            ])
        );
    }

    public function resetForm()
    {
        $this->reset();
        $this->professor = null;
    }
}
