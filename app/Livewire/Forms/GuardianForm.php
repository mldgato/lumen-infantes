<?php

namespace App\Livewire\Forms;

use Livewire\Form;
use App\Models\Guardian;
use Illuminate\Validation\Rule;

class GuardianForm extends Form
{
    public ?Guardian $guardian = null;

    public $first_name = '';
    public $last_name = '';
    public $birthplace = '';
    public $birthdate = '';
    public $nationality = '';
    public $cui = '';
    public $cui_extended_in = '';
    public $profession = '';
    public $residence_address = '';
    public $phone = '';
    public $email = '';
    public $company_name = '';
    public $company_address = '';
    public $company_phone = '';

    public function rules()
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'birthplace' => 'nullable|string|max:255',
            'birthdate' => 'nullable|date',
            'nationality' => 'nullable|string|max:255',
            'cui' => [
                'required',
                'string',
                'max:20',
                Rule::unique('guardians', 'cui')->ignore($this->guardian)
            ],
            'cui_extended_in' => 'nullable|string|max:255',
            'profession' => 'nullable|string|max:255',
            'residence_address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'company_name' => 'nullable|string|max:255',
            'company_address' => 'nullable|string|max:500',
            'company_phone' => 'nullable|string|max:20',
        ];
    }

    public function setGuardian(Guardian $guardian)
    {
        $this->guardian = $guardian;
        $this->first_name = $guardian->first_name;
        $this->last_name = $guardian->last_name;
        $this->birthplace = $guardian->birthplace;
        $this->birthdate = $guardian->birthdate ? $guardian->birthdate->format('Y-m-d') : '';
        $this->nationality = $guardian->nationality;
        $this->cui = $guardian->cui;
        $this->cui_extended_in = $guardian->cui_extended_in;
        $this->profession = $guardian->profession;
        $this->residence_address = $guardian->residence_address;
        $this->phone = $guardian->phone;
        $this->email = $guardian->email;
        $this->company_name = $guardian->company_name;
        $this->company_address = $guardian->company_address;
        $this->company_phone = $guardian->company_phone;
    }

    public function store()
    {
        $this->validate();
        return Guardian::create($this->all());
    }

    public function update()
    {
        $this->validate();
        $this->guardian->update($this->all());
        return $this->guardian;
    }

    public function resetForm()
    {
        $this->reset();
        $this->guardian = null;
    }
}
