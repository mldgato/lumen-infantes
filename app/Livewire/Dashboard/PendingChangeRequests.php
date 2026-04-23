<?php

namespace App\Livewire\Dashboard;

use App\Models\GradeChangeRequest;
use Livewire\Component;

class PendingChangeRequests extends Component
{
    public bool $readyToLoad = false;

    public array $recentPendingRequests = [];

    public function loadData(): void
    {
        $this->recentPendingRequests = GradeChangeRequest::with([
            'professor.user',
            'gradeBook.assignment.classroom.grade',
            'gradeBook.assignment.classroom.section',
            'gradeBook.assignment.pensumCourse.course',
        ])
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'professor' => $r->professor->user->name,
                'grade' => $r->gradeBook->assignment->classroom->grade->grade_name,
                'section' => $r->gradeBook->assignment->classroom->section->section_name,
                'course' => $r->gradeBook->assignment->pensumCourse->course->course_name,
                'created_at' => $r->created_at->diffForHumans(),
            ])
            ->toArray();

        $this->readyToLoad = true;
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.dashboard.pending-change-requests');
    }
}
