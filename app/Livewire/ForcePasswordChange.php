<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Services\AuditService;

class ForcePasswordChange extends Component
{
    public $password;
    public $password_confirmation;

    protected $rules = [
        'password' => 'required|min:8|confirmed',
    ];

    protected $messages = [
        'password.required' => 'La contraseña es obligatoria.',
        'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        'password.confirmed' => 'Las contraseñas no coinciden.',
    ];

    public function updatePassword()
    {
        $this->validate();

        $user = Auth::user();

        // Actualizamos la contraseña y quitamos la bandera para liberarlo del middleware
        $user->password = Hash::make($this->password);
        $user->must_change_password = false;
        $user->save();

        // Registramos en tu log de auditoría
        AuditService::passwordChanged($user, true);

        // Redirigimos al dashboard original
        return redirect()->route('dashboard');
    }

    public function render()
    {
        // Extendemos la página de autenticación de AdminLTE
        // Esto cargará automáticamente el logo y los estilos de caja centrada
        return view('livewire.force-password-change')
            ->extends('adminlte::auth.auth-page', ['auth_type' => 'login'])
            ->section('auth_body');
    }
}
