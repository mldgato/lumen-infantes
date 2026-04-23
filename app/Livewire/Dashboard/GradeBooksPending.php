<?php

namespace App\Livewire\Dashboard;

use App\Models\GradeBook;
use Livewire\Component;

class GradeBooksPending extends Component
{
    public bool $readyToLoad = false;

    public int $pendingCount = 0;

    public function loadData(): void
    {
        $year = date('Y');

        $this->pendingCount = GradeBook::where('status', 'locked')
            ->whereHas(
                'assignment',
                fn ($q) => $q->whereHas('classroom', fn ($q) => $q->where('year', $year))
            )->count();

        $this->readyToLoad = true;
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.dashboard.grade-books-pending');
    }
}
