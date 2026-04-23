<?php

namespace App\Livewire\Dashboard;

use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Component;

class BirthdayStudents extends Component
{
    public bool $readyToLoad = false;

    public array $birthdayStudents = [];

    public function loadData(): void
    {
        $year = date('Y');
        $month = now()->month;

        $this->birthdayStudents = User::role('Estudiante')
            ->whereNotNull('birthdate')
            ->whereMonth('birthdate', $month)
            ->whereHas(
                'student.enrollments',
                fn ($q) => $q->where('status', 'Activo')
                    ->whereHas('classroom', fn ($q2) => $q2->where('year', $year))
            )
            ->orderByRaw('DAY(birthdate)')
            ->get()
            ->map(fn ($u) => [
                'name' => Str::limit($u->name, 20),
                'day' => $u->birthdate->day,
                'age' => now()->year - $u->birthdate->year,
                'initials' => $u->initials(),
                'image' => $u->adminlte_image(),
                'is_today' => $u->birthdate->day === now()->day,
            ])
            ->toArray();

        $this->readyToLoad = true;
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.dashboard.birthday-students');
    }
}
