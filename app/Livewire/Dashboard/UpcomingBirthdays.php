<?php

namespace App\Livewire\Dashboard;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Livewire\Component;

class UpcomingBirthdays extends Component
{
    public bool $readyToLoad = false;

    public array $upcomingBirthdays = [];

    public function loadData(): void
    {
        $this->upcomingBirthdays = User::whereNotNull('birthdate')
            ->whereDoesntHave(
                'roles',
                fn ($q) => $q->whereIn('name', ['Estudiante', 'Super Administrador'])
            )
            ->selectRaw("*, DATEDIFF(
                DATE(CONCAT(
                    IF(
                        DATE_FORMAT(birthdate, '%m-%d') >= DATE_FORMAT(NOW(), '%m-%d'),
                        YEAR(NOW()),
                        YEAR(NOW()) + 1
                    ),
                    '-',
                    DATE_FORMAT(birthdate, '%m-%d')
                )),
                DATE(NOW())
            ) as days_until")
            ->orderBy('days_until')
            ->take(4)
            ->get()
            ->map(fn ($u) => [
                'name' => Str::limit($u->name, 24),
                'role' => $u->roles()->first()?->name ?? 'Sin rol',
                'initials' => $u->initials(),
                'image' => $u->adminlte_image(),
                'day' => $u->birthdate->day,
                'month' => ucfirst(Carbon::parse($u->birthdate)->locale('es')->isoFormat('MMMM')),
                'days_until' => (int) $u->days_until,
                'is_today' => (int) $u->days_until === 0,
            ])
            ->toArray();

        $this->readyToLoad = true;
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.dashboard.upcoming-birthdays');
    }
}
