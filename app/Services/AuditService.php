<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\GradeBook;
use App\Models\GradeBookScore;
use App\Models\GradeChangeRequest;
use App\Models\StudentEnrollment;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    public static function log(
        string  $event,
        string  $module,
        string  $description,
        ?Model  $auditable  = null,
        ?array  $oldValues  = null,
        ?array  $newValues  = null,
        ?int    $userId     = null,
    ): void {
        try {
            AuditLog::create([
                'user_id'        => $userId ?? Auth::id(),
                'event'          => $event,
                'module'         => $module,
                'description'    => $description,
                'auditable_type' => $auditable ? get_class($auditable) : null,
                'auditable_id'   => $auditable?->id,
                'old_values'     => $oldValues,
                'new_values'     => $newValues,
                'ip_address'     => Request::ip(),
            ]);
        } catch (\Throwable $e) {
            logger()->error('AuditService error: ' . $e->getMessage());
        }
    }

    public static function gradeBookStatusChanged(
        GradeBook $gradeBook,
        string $oldStatus,
        string $newStatus,
        ?string $reason = null,
    ): void {
        $statusLabels = [
            'open'     => 'Abierto',
            'locked'   => 'Enviado a revisión',
            'approved' => 'Aprobado',
            'rejected' => 'Rechazado',
        ];

        $description = sprintf(
            'Cuadro de "%s" — Unidad %d — %s %s cambió de "%s" a "%s"',
            $gradeBook->assignment->pensumCourse->course->course_name ?? '—',
            $gradeBook->assignment->unit ?? '—',
            $gradeBook->assignment->classroom->grade->grade_name ?? '—',
            $gradeBook->assignment->classroom->section->section_name ?? '—',
            $statusLabels[$oldStatus] ?? $oldStatus,
            $statusLabels[$newStatus] ?? $newStatus,
        );

        $newValues = ['status' => $newStatus];
        if ($reason) $newValues['rejection_reason'] = $reason;

        self::log(
            event: 'status_changed',
            module: 'Cuadros',
            description: $description,
            auditable: $gradeBook,
            oldValues: ['status' => $oldStatus],
            newValues: $newValues,
        );
    }

    public static function scoreChanged(
        GradeBookScore $score,
        ?float $oldScore,
        float $newScore,
    ): void {
        $studentName  = $score->student->user->name ?? '—';
        $activityName = $score->activity->name ?? '—';

        self::log(
            event: 'score_updated',
            module: 'Calificaciones',
            description: "Nota de \"{$activityName}\" para {$studentName} cambió de {$oldScore} a {$newScore}",
            auditable: $score,
            oldValues: ['score' => $oldScore],
            newValues: ['score' => $newScore],
        );
    }

    public static function enrollmentCreated(StudentEnrollment $enrollment): void
    {
        $studentName = $enrollment->student->user->name ?? '—';
        $classroom   = $enrollment->classroom->grade->grade_name . ' '
            . $enrollment->classroom->section->section_name . ' '
            . $enrollment->classroom->year;

        self::log(
            event: 'enrolled',
            module: 'Inscripciones',
            description: "{$studentName} fue inscrito en {$classroom}",
            auditable: $enrollment,
            newValues: ['status' => $enrollment->status, 'classroom' => $classroom],
        );
    }

    public static function enrollmentStatusChanged(
        StudentEnrollment $enrollment,
        string $oldStatus,
        string $newStatus,
    ): void {
        $studentName = $enrollment->student->user->name ?? '—';

        self::log(
            event: 'enrollment_status_changed',
            module: 'Inscripciones',
            description: "Estado de inscripción de {$studentName} cambió de {$oldStatus} a {$newStatus}",
            auditable: $enrollment,
            oldValues: ['status' => $oldStatus],
            newValues: ['status' => $newStatus],
        );
    }

    public static function userCreated(User $user): void
    {
        self::log(
            event: 'created',
            module: 'Usuarios',
            description: "Usuario \"{$user->name}\" fue creado",
            auditable: $user,
            newValues: ['email' => $user->email, 'cui' => $user->cui],
        );
    }

    public static function userUpdated(User $user, array $changed): void
    {
        $fields = implode(', ', array_keys($changed));
        self::log(
            event: 'updated',
            module: 'Usuarios',
            description: "Usuario \"{$user->name}\" fue actualizado — campos: {$fields}",
            auditable: $user,
            oldValues: array_map(fn($c) => $c['old'] ?? null, $changed),
            newValues: array_map(fn($c) => $c['new'] ?? null, $changed),
        );
    }

    public static function gradeChangeRequestCreated(GradeChangeRequest $request): void
    {
        self::log(
            event: 'created',
            module: 'Cambio de Notas',
            description: "Solicitud de cambio de notas creada para cuadro ID {$request->grade_book_id}",
            auditable: $request,
            newValues: ['grade_book_id' => $request->grade_book_id, 'status' => 'pending'],
        );
    }

    public static function gradeChangeRequestResolved(
        GradeChangeRequest $request,
        string $resolution,
        ?string $reason = null,
    ): void {
        $description = "Solicitud de cambio de notas {$resolution}";
        if ($reason) $description .= " — motivo: {$reason}";

        self::log(
            event: $resolution === 'approved' ? 'approved' : 'rejected',
            module: 'Cambio de Notas',
            description: $description,
            auditable: $request,
            oldValues: ['status' => 'pending'],
            newValues: ['status' => $resolution, 'reason' => $reason],
        );
    }

    public static function configChanged(string $description, array $old = [], array $new = []): void
    {
        self::log(
            event: 'config_changed',
            module: 'Configuración',
            description: $description,
            oldValues: $old ?: null,
            newValues: $new ?: null,
        );
    }
}
