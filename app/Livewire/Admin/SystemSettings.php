<?php

namespace App\Livewire\Admin;

use App\Models\SystemSetting;
use Livewire\Component;

class SystemSettings extends Component
{
    public string $enrollmentMode = 'direct';

    public function mount(): void
    {
        $this->authorize('admin.settings.index');
        $this->enrollmentMode = SystemSetting::get('enrollment_mode', 'direct');
    }

    public function save(): void
    {
        $this->authorize('admin.settings.index');

        $this->validate(
            ['enrollmentMode' => ['required', 'in:direct,admissions']],
            [
                'enrollmentMode.required' => 'Seleccione un modo de inscripción.',
                'enrollmentMode.in' => 'Modo de inscripción no válido.',
            ],
        );

        SystemSetting::set('enrollment_mode', $this->enrollmentMode);

        $this->dispatch('toastMessage', title: 'Configuración guardada correctamente.', icon: 'success');
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.admin.system-settings');
    }
}
