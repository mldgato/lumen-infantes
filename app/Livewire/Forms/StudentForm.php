<?php

namespace App\Livewire\Forms;

use Livewire\Form;
use App\Models\Student;
use Illuminate\Validation\Rule;

class StudentForm extends Form
{
    public ?Student $student = null;

    public $personal_code = '';
    public $carne = '';
    public $is_own_guardian = false;

    public function rules()
    {
        return [
            'personal_code' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('students', 'personal_code')->ignore($this->student)
            ],
            'carne' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('students', 'carne')->ignore($this->student)
            ],
            'is_own_guardian' => 'boolean',
        ];
    }

    public function setStudent(?Student $student)
    {
        if ($student) {
            $this->student = $student;
            $this->personal_code = $student->personal_code;
            $this->carne = $student->carne;
            $this->is_own_guardian = $student->is_own_guardian;
        } else {
            $this->resetForm();
        }
    }

    public function save($userId)
    {
        $this->validate();

        Student::updateOrCreate(
            ['user_id' => $userId],
            [
                'personal_code' => $this->personal_code,
                'carne' => $this->carne,
                'is_own_guardian' => $this->is_own_guardian,
            ]
        );
    }

    public function resetForm()
    {
        $this->reset();
        $this->student = null;
    }
}
