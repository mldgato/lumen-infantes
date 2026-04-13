<?php

namespace App\Livewire\Profile;

use App\Services\AuditService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class UpdateProfile extends Component
{
    use WithFileUploads;

    public $current_password;

    public $password;

    public $password_confirmation;

    public $photo; // Variable para la nueva imagen

    public function updatePassword()
    {
        $this->validate([
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        Auth::user()->update([
            'password' => Hash::make($this->password),
        ]);

        AuditService::passwordChanged(Auth::user());

        // 1. Mensaje flash para SweetAlert
        session()->flash('password_message', 'La contraseña ha sido actualizada correctamente.');

        // 2. Redirección para limpiar campos y disparar el script en la vista
        $this->redirect(route('profile'));
    }

    public function updatedPhoto()
    {
        $this->validate([
            'photo' => ['required', 'image', 'max:2048'],
        ]);

        $user = Auth::user();

        $path = $this->photo->store('userImages', 'public');

        if ($user->image) {
            Storage::disk('public')->delete($user->image->url);
            $user->image()->update(['url' => $path]);
        } else {
            $user->image()->create(['url' => $path]);
        }

        AuditService::log(
            event: 'updated',
            module: 'Perfil',
            description: "Usuario \"{$user->name}\" actualizó su foto de perfil",
            auditable: $user,
        );

        session()->flash('image_message', 'Imagen de perfil actualizada.');
        $this->redirect(route('profile'));
    }

    public function updated($propertyName)
    {
        // Valida únicamente el campo que el usuario acaba de modificar
        $this->validateOnly($propertyName, [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    public function render()
    {
        return view('livewire.profile.update-profile');
    }
}
