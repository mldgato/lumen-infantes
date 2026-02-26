<?php

namespace App\Livewire\Forms;

use Livewire\Form;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserForm extends Form
{
    public ?User $user = null;

    public $cui = '';
    public $first_name = '';
    public $middle_name = '';
    public $surname = '';
    public $second_surname = '';
    public $married_surname = '';
    public $civil_status = '';
    public $birthdate = '';
    public $gender = '';
    public $email = '';
    public $password = '';
    public $cellphone = '';
    public $personal_email = '';
    public $address = '';
    public $is_active = 1;

    public function rules()
    {
        return [
            'cui' => ['required', 'string', 'max:20', Rule::unique('users', 'cui')->ignore($this->user)],
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'surname' => 'required|string|max:255',
            'second_surname' => 'nullable|string|max:255',
            'married_surname' => 'nullable|string|max:255',
            'civil_status' => 'required|string|max:50',
            'birthdate' => 'required|date',
            'gender' => 'required|string|max:50',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user)],
            'password' => $this->user ? 'nullable|min:8' : 'required|min:8',
            'cellphone' => 'nullable|string|max:20',
            'personal_email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'is_active' => 'required|boolean',
        ];
    }

    public function setUser(User $user)
    {
        $this->user = $user;
        $this->cui = $user->cui;
        $this->first_name = $user->first_name;
        $this->middle_name = $user->middle_name;
        $this->surname = $user->surname;
        $this->second_surname = $user->second_surname;
        $this->married_surname = $user->married_surname;
        $this->civil_status = $user->civil_status;
        $this->birthdate = $user->birthdate ? $user->birthdate->format('Y-m-d') : '';
        $this->gender = $user->gender;
        $this->email = $user->email;
        $this->cellphone = $user->cellphone;
        $this->personal_email = $user->personal_email;
        $this->address = $user->address;
        $this->is_active = $user->is_active;
        // La contraseña no se carga por seguridad
    }

    public function store()
    {
        $this->validate();

        $data = $this->all();
        $data['password'] = Hash::make($this->password);

        return User::create($data);
    }

    public function update()
    {
        $this->validate();

        $data = $this->all();

        // Solo actualizamos la contraseña si el usuario escribió una nueva
        if (empty($this->password)) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($this->password);
        }

        $this->user->update($data);
        return $this->user;
    }

    public function resetForm()
    {
        $this->reset();
        $this->user = null;
        $this->is_active = 1;
    }
}
