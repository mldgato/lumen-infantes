<?php

namespace App\Livewire\Admin;

use App\Models\GradeBook;
use App\Models\GradeBookScore;
use App\Models\GradeBookTotal;
use App\Models\GradeBookActivity;
use App\Models\GradeChangeRequest;
use App\Models\GradeChangeRequestItem;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use App\Services\AuditService;

class GradeChangeRequests extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public bool $readyToLoad    = false;
    public string $filterStatus = 'pending';
    public string $search       = '';
    public string $cant         = '10';

    // Detail view
    public ?GradeChangeRequest $viewingRequest = null;

    // Rejection
    public ?int $rejectingId        = null;
    public string $rejectionReason  = '';

    protected $queryString = [
        'filterStatus' => ['except' => 'pending'],
        'search'       => ['except' => ''],
        'cant'         => ['except' => '10'],
    ];

    public function loadRequests(): void
    {
        $this->readyToLoad = true;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    // ==========================================
    // DETAIL VIEW
    // ==========================================

    public function openRequest(int $id): void
    {
        $this->viewingRequest = GradeChangeRequest::with([
            'gradeBook.assignment.classroom.level',
            'gradeBook.assignment.classroom.grade',
            'gradeBook.assignment.classroom.section',
            'gradeBook.assignment.pensumCourse.course',
            'gradeBook.assignment.professor.user',
            'professor.user',
            'items.student.user',
            'items.activity.activityType',
            'reviewer',
        ])->findOrFail($id);
    }

    public function closeRequest(): void
    {
        $this->viewingRequest  = null;
        $this->rejectingId     = null;
        $this->rejectionReason = '';
        $this->resetValidation();
    }

    // ==========================================
    // APPROVE
    // ==========================================

    public function approve(int $requestId): void
    {
        $request = GradeChangeRequest::with([
            'items',
            'gradeBook.activities',
        ])->findOrFail($requestId);

        DB::transaction(function () use ($request) {
            foreach ($request->items as $item) {
                $score = GradeBookScore::where('grade_book_activity_id', $item->grade_book_activity_id)
                    ->where('student_id', $item->student_id)
                    ->first();

                if ($score) {
                    $oldScore = (float) $score->score;
                    $score->update(['score' => $item->new_score]);
                    AuditService::scoreChanged(
                        $score->load('student.user', 'activity'),
                        $oldScore,
                        (float) $item->new_score
                    );
                }
            }

            // Recalcular totales
            $studentIds = $request->items->pluck('student_id')->unique();
            foreach ($studentIds as $studentId) {
                $this->recalculateTotal($request->gradeBook, $studentId);
            }

            $request->update(['status' => 'approved']);

            AuditService::gradeChangeRequestResolved($request, 'approved');
        });

        $this->dispatch('closeModalMessaje', [
            'title'   => '¡Aprobado!',
            'message' => 'La solicitud fue aprobada y las notas actualizadas.',
            'type'    => 'success',
            'modalId' => 'GradeChangeModal',
        ]);
    }

    // ==========================================
    // REJECT
    // ==========================================

    public function openRejectModal(int $id): void
    {
        $this->rejectingId     = $id;
        $this->rejectionReason = '';
        $this->resetValidation();
    }

    public function reject(): void
    {
        $this->validate([
            'rejectionReason' => 'required|string|min:5',
        ], [
            'rejectionReason.required' => 'El motivo de rechazo es obligatorio.',
            'rejectionReason.min'      => 'El motivo debe tener al menos 5 caracteres.',
        ]);

        $request = GradeChangeRequest::findOrFail($this->rejectingId);
        $request->update([
            'status'           => 'rejected',
            'rejection_reason' => $this->rejectionReason,
        ]);

        AuditService::gradeChangeRequestResolved($request, 'rejected', $this->rejectionReason);

        $this->rejectionReason = '';
        $this->rejectingId     = null;

        $this->dispatch('closeModalMessaje', [
            'title'   => 'Rechazado',
            'message' => 'La solicitud fue rechazada.',
            'type'    => 'warning',
            'modalId' => 'RejectModal',
        ]);
    }

    public function render()
    {
        $requests = $this->readyToLoad
            ? GradeChangeRequest::with([
                'gradeBook.assignment.classroom.grade',
                'gradeBook.assignment.classroom.section',
                'gradeBook.assignment.pensumCourse.course',
                'professor.user',
                'reviewer',
                'items',
            ])
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->where(function ($q) {
                $q->whereHas('gradeBook.assignment.classroom.grade', fn($q) =>
                $q->where('grade_name', 'like', '%' . $this->search . '%'))
                    ->orWhereHas('gradeBook.assignment.pensumCourse.course', fn($q) =>
                    $q->where('course_name', 'like', '%' . $this->search . '%'))
                    ->orWhereHas('professor.user', fn($q) =>
                    $q->where('name', 'like', '%' . $this->search . '%'));
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->cant)
            : [];

        $students = $this->viewingRequest
            ? Student::whereHas(
                'enrollments',
                fn($q) =>
                $q->where('classroom_id', $this->viewingRequest->gradeBook->assignment->classroom_id)
                    ->where('status', 'Activo')
            )
            ->join('users', 'students.user_id', '=', 'users.id')
            ->orderBy('users.surname')
            ->orderBy('users.second_surname')
            ->orderBy('users.first_name')
            ->orderBy('users.middle_name')
            ->select('students.*')
            ->with('user')
            ->get()
            : collect();

        return view('livewire.admin.grade-change-requests', compact('requests', 'students'));
    }
}
