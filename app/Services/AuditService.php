<?php

namespace App\Services;

use App\Models\AdmissionApplication;
use App\Models\AdmissionApplicationDocument;
use App\Models\AdmissionApplicationStatus;
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
        string $event,
        string $module,
        string $description,
        ?Model $auditable = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?int $userId = null,
    ): void {
        try {
            AuditLog::create([
                'user_id' => $userId ?? Auth::id(),
                'event' => $event,
                'module' => $module,
                'description' => $description,
                'auditable_type' => $auditable ? get_class($auditable) : null,
                'auditable_id' => $auditable?->id,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'ip_address' => Request::ip(),
            ]);
        } catch (\Throwable $e) {
            logger()->error('AuditService error: '.$e->getMessage());
        }
    }

    public static function gradeBookStatusChanged(
        GradeBook $gradeBook,
        string $oldStatus,
        string $newStatus,
        ?string $reason = null,
    ): void {
        $statusLabels = [
            'open' => 'Abierto',
            'locked' => 'Enviado a revisión',
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
        if ($reason) {
            $newValues['rejection_reason'] = $reason;
        }

        self::log(
            event: 'status_changed',
            module: 'Cuadros',
            description: $description,
            auditable: $gradeBook,
            oldValues: ['status' => $oldStatus],
            newValues: $newValues,
        );
    }

    public static function gradeScoresCopied(
        GradeBook $targetGradeBook,
        array $oldScores,
        array $newScores,
        string $originCourseName,
        int $originUnit,
    ): void {
        $targetGradeBook->loadMissing([
            'assignment.pensumCourse.course',
            'assignment.classroom.grade',
            'assignment.classroom.section',
        ]);

        $assignment = $targetGradeBook->assignment;
        $destCourseName = $assignment->pensumCourse->course->course_name ?? '—';
        $destUnit = $assignment->unit ?? '—';
        $gradeName = $assignment->classroom->grade->grade_name ?? '—';
        $sectionName = $assignment->classroom->section->section_name ?? '—';

        $description = sprintf(
            'Notas copiadas de "%s" Unidad %d → "%s" Unidad %d — %s %s',
            $originCourseName,
            $originUnit,
            $destCourseName,
            $destUnit,
            $gradeName,
            $sectionName,
        );

        self::log(
            event: 'scores_copied',
            module: 'Cuadros',
            description: $description,
            auditable: $targetGradeBook,
            oldValues: $oldScores ?: null,
            newValues: $newScores ?: null,
        );
    }

    public static function scoreChanged(
        GradeBookScore $score,
        ?float $oldScore,
        float $newScore,
    ): void {
        $studentName = $score->student->user->name ?? '—';
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
        $classroom = $enrollment->classroom->grade->grade_name.' '
            .$enrollment->classroom->section->section_name.' '
            .$enrollment->classroom->year;

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
            oldValues: array_map(fn ($c) => $c['old'] ?? null, $changed),
            newValues: array_map(fn ($c) => $c['new'] ?? null, $changed),
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
        if ($reason) {
            $description .= " — motivo: {$reason}";
        }

        self::log(
            event: $resolution === 'approved' ? 'approved' : 'rejected',
            module: 'Cambio de Notas',
            description: $description,
            auditable: $request,
            oldValues: ['status' => 'pending'],
            newValues: ['status' => $resolution, 'reason' => $reason],
        );
    }

    public static function professorProfileUpdated(User $user, array $changed): void
    {
        $fields = implode(', ', array_keys($changed));
        self::log(
            event: 'updated',
            module: 'Perfil',
            description: "Información docente de \"{$user->name}\" fue actualizada — campos: {$fields}",
            auditable: $user->professor,
            oldValues: array_map(fn ($c) => $c['old'] ?? null, $changed),
            newValues: array_map(fn ($c) => $c['new'] ?? null, $changed),
        );
    }

    public static function medicalRecordUpdated(User $user, array $changed): void
    {
        $fields = implode(', ', array_keys($changed));
        self::log(
            event: 'updated',
            module: 'Perfil',
            description: "Ficha médica de \"{$user->name}\" fue actualizada — campos: {$fields}",
            auditable: $user->medicalRecord,
            oldValues: array_map(fn ($c) => $c['old'] ?? null, $changed),
            newValues: array_map(fn ($c) => $c['new'] ?? null, $changed),
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

    public static function passwordChanged(User $user, bool $forced = false): void
    {
        $description = $forced
            ? "El usuario \"{$user->name}\" cambió su contraseña (Cambio obligatorio)."
            : "El usuario \"{$user->name}\" cambió su contraseña.";

        self::log(
            event: 'password_changed',
            module: 'Seguridad',
            description: $description,
            auditable: $user,
            // Omitimos old_values y new_values por seguridad, no debemos registrar hashes de contraseñas.
        );
    }

    // ── Admisiones ───────────────────────────────────────────────

    public static function admissionStatusChanged(
        AdmissionApplication $app,
        string $oldStatus,
        string $newStatus,
        ?string $notes = null,
    ): void {
        $description = sprintf(
            'Solicitud de "%s" cambió de estado "%s" a "%s"',
            $app->fullStudentName(),
            AdmissionApplicationStatus::labelFor($oldStatus),
            AdmissionApplicationStatus::labelFor($newStatus),
        );

        if ($notes) {
            $description .= " — nota: {$notes}";
        }

        $newValues = ['status' => $newStatus];
        if ($notes) {
            $newValues['notes'] = $notes;
        }

        self::log(
            event: 'status_changed',
            module: 'Admisiones',
            description: $description,
            auditable: $app,
            oldValues: ['status' => $oldStatus],
            newValues: $newValues,
        );
    }

    public static function admissionUpdated(
        AdmissionApplication $app,
        array $oldValues,
        array $newValues,
    ): void {
        if (empty($newValues)) {
            return;
        }

        $fields = implode(', ', array_keys($newValues));

        self::log(
            event: 'updated',
            module: 'Admisiones',
            description: sprintf('Solicitud de "%s" fue actualizada — campos: %s', $app->fullStudentName(), $fields),
            auditable: $app,
            oldValues: $oldValues ?: null,
            newValues: $newValues,
        );
    }

    public static function admissionDocumentToggled(
        AdmissionApplication $app,
        string $field,
        bool $newValue,
    ): void {
        $label = AdmissionApplicationDocument::fields()[$field] ?? $field;
        $action = $newValue ? 'marcado' : 'desmarcado';

        self::log(
            event: 'document_toggled',
            module: 'Admisiones',
            description: sprintf('Documento "%s" %s en solicitud de "%s"', $label, $action, $app->fullStudentName()),
            auditable: $app,
            oldValues: [$field => ! $newValue],
            newValues: [$field => $newValue],
        );
    }

    public static function admissionUnlocked(
        AdmissionApplication $app,
        string $section,
    ): void {
        $sectionLabel = match ($section) {
            'billing' => 'Facturación',
            'psychometric' => 'Psicométrica',
            'academic' => 'Académico',
            default => $section,
        };

        self::log(
            event: 'unlocked',
            module: 'Admisiones',
            description: sprintf('Sección "%s" desbloqueada para corrección en solicitud de "%s"', $sectionLabel, $app->fullStudentName()),
            auditable: $app,
            newValues: ["{$section}_unlocked" => true],
        );
    }

    public static function admissionBillingSaved(
        AdmissionApplication $app,
        bool $isCorrection,
        string $invoiceNumber,
        string $invoiceDate,
    ): void {
        $action = $isCorrection ? 'corregida' : 'registrada';

        self::log(
            event: $isCorrection ? 'billing_corrected' : 'billing_registered',
            module: 'Admisiones',
            description: sprintf('Factura %s para solicitud de "%s" — No. %s', $action, $app->fullStudentName(), $invoiceNumber),
            auditable: $app,
            newValues: ['invoice_number' => $invoiceNumber, 'invoice_date' => $invoiceDate],
        );
    }

    public static function admissionPsychometricSaved(
        AdmissionApplication $app,
        bool $isCorrection,
        string $result,
    ): void {
        $action = $isCorrection ? 'corregida' : 'registrada';

        self::log(
            event: $isCorrection ? 'psychometric_corrected' : 'psychometric_registered',
            module: 'Admisiones',
            description: sprintf('Evaluación psicométrica %s para solicitud de "%s" — Resultado: %s', $action, $app->fullStudentName(), $result),
            auditable: $app,
            newValues: ['result' => $result],
        );
    }

    public static function admissionScoreChanged(
        AdmissionApplication $app,
        string $courseName,
        float|string $score,
        string $action,
    ): void {
        $actionLabel = $action === 'added' ? 'agregada' : 'eliminada';

        self::log(
            event: "score_{$action}",
            module: 'Admisiones',
            description: sprintf('Materia "%s" %s con punteo %s en solicitud de "%s"', $courseName, $actionLabel, $score, $app->fullStudentName()),
            auditable: $app,
            newValues: ['course' => $courseName, 'score' => $score],
        );
    }

    public static function admissionEvaluationFinalized(
        AdmissionApplication $app,
        bool $isCorrection,
        int $courseCount,
    ): void {
        $action = $isCorrection ? 'corregida' : 'finalizada';

        self::log(
            event: $isCorrection ? 'evaluation_corrected' : 'evaluation_finalized',
            module: 'Admisiones',
            description: sprintf('Evaluación académica %s para solicitud de "%s" — %d materia(s)', $action, $app->fullStudentName(), $courseCount),
            auditable: $app,
            newValues: ['course_count' => $courseCount],
        );
    }

    public static function admissionReportDownloaded(array $filters): void
    {
        $user = Auth::user();
        $filterDesc = collect($filters)
            ->filter(fn ($v) => $v !== '' && $v !== null && $v !== [])
            ->map(fn ($v, $k) => "{$k}: {$v}")
            ->implode(', ');

        self::log(
            event: 'report_downloaded',
            module: 'Admisiones',
            description: sprintf(
                'Reporte Excel de admisiones descargado por "%s"%s',
                $user?->name ?? 'Sistema',
                $filterDesc ? " — filtros: {$filterDesc}" : '',
            ),
            newValues: array_filter($filters, fn ($v) => $v !== '' && $v !== null && $v !== []),
        );
    }
}
