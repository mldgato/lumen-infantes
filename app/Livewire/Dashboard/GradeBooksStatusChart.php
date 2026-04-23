<?php

namespace App\Livewire\Dashboard;

use App\Models\GradeBook;
use Livewire\Component;

class GradeBooksStatusChart extends Component
{
    public bool $readyToLoad = false;

    public array $gradeBookStatuses = [];

    public function loadData(): void
    {
        $year = date('Y');

        $statuses = GradeBook::whereHas(
            'assignment',
            fn ($q) => $q->whereHas('classroom', fn ($q) => $q->where('year', $year))
        )->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $this->gradeBookStatuses = [
            'open' => $statuses['open'] ?? 0,
            'locked' => $statuses['locked'] ?? 0,
            'approved' => $statuses['approved'] ?? 0,
            'rejected' => $statuses['rejected'] ?? 0,
        ];

        $this->readyToLoad = true;
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.dashboard.grade-books-status-chart');
    }
}
