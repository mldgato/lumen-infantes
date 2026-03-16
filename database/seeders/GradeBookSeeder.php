<?php

namespace Database\Seeders;

use App\Models\AcademicConfiguration;
use App\Models\ClassroomCourseAssignment;
use App\Models\GradeBook;
use App\Models\GradeBookActivity;
use App\Models\GradeBookScore;
use App\Models\GradeBookTotal;
use App\Models\Student;
use Illuminate\Database\Seeder;

class GradeBookSeeder extends Seeder
{
    // Distribución de estados: 50% aprobado, 20% abierto, 15% en revisión, 15% rechazado
    private array $statusPool = [
        'approved',
        'approved',
        'approved',
        'approved',
        'approved',
        'open',
        'open',
        'locked',
        'locked',
        'rejected',
    ];

    private array $rejectionReasons = [
        'Las calificaciones no cuadran con el registro físico.',
        'Falta completar actividades de mejora.',
        'Se detectaron errores en los punteos ingresados.',
        'El total de puntos excede el máximo permitido.',
        'Revisar las notas de la prueba final.',
    ];

    public function run(): void
    {
        $config = AcademicConfiguration::with('activities.activityType')->first();

        if (! $config) {
            $this->command->warn('No existe configuración académica. Omitiendo GradeBookSeeder.');
            return;
        }

        $activityTemplate = [];
        foreach ($config->activities as $configActivity) {
            $names = [];
            for ($i = 1; $i <= $configActivity->quantity; $i++) {
                $names[] = $configActivity->activityType->name . ' ' . $i;
            }
            $activityTemplate[$configActivity->activity_type_id] = [
                'names'      => $names,
                'max_points' => $configActivity->points_each,
                'is_extra'   => $configActivity->activityType->is_extra,
            ];
        }

        $assignments = ClassroomCourseAssignment::with('classroom')->get();
        $poolSize    = count($this->statusPool);
        $index       = 0;

        foreach ($assignments as $assignment) {
            // Rotar sobre el pool de estados
            $status = $this->statusPool[$index % $poolSize];
            $index++;

            $rejectionReason = $status === 'rejected'
                ? fake()->randomElement($this->rejectionReasons)
                : null;

            $gradeBook = GradeBook::firstOrCreate(
                ['classroom_course_assignment_id' => $assignment->id],
                [
                    'academic_configuration_id' => $config->id,
                    'status'                    => $status,
                    'rejection_reason'          => $rejectionReason,
                ]
            );

            // Si ya existe, actualizar estado para reflejar la distribución
            if ($gradeBook->wasRecentlyCreated === false) {
                $gradeBook->update([
                    'status'           => $status,
                    'rejection_reason' => $rejectionReason,
                ]);
            }

            // Crear actividades si no las tiene
            if ($gradeBook->activities()->count() === 0) {
                $ordering   = 1;
                $activities = [];

                foreach ($activityTemplate as $typeId => $data) {
                    foreach ($data['names'] as $name) {
                        $activities[] = GradeBookActivity::create([
                            'grade_book_id'    => $gradeBook->id,
                            'activity_type_id' => $typeId,
                            'name'             => $name,
                            'max_points'       => $data['max_points'],
                            'ordering'         => $ordering++,
                        ]);
                    }
                }
            } else {
                $activities = $gradeBook->activities()->get()->all();
            }

            // Solo crear scores si el cuadro no está abierto (abierto = sin notas aún)
            if ($status === 'open') {
                continue;
            }

            $students = Student::whereHas(
                'enrollments',
                fn($q) =>
                $q->where('classroom_id', $assignment->classroom_id)
                    ->where('status', 'Activo')
            )->get();

            foreach ($activities as $activity) {
                foreach ($students as $student) {
                    // Cuadros rechazados tienen scores pero con algunos valores bajos
                    $minScore = $status === 'rejected'
                        ? (float) $activity->max_points * 0.2
                        : (float) $activity->max_points * 0.5;

                    GradeBookScore::firstOrCreate(
                        [
                            'grade_book_activity_id' => $activity->id,
                            'student_id'             => $student->id,
                        ],
                        [
                            'score'             => fake()->randomFloat(2, $minScore, (float) $activity->max_points),
                            'improvement_score' => null,
                        ]
                    );
                }
            }

            // Calcular totales
            $allActivities = $gradeBook->activities()->with(['scores', 'activityType'])->get();

            foreach ($students as $student) {
                $normalPoints = 0;
                $extraPoints  = 0;

                foreach ($allActivities as $activity) {
                    $score     = $activity->scores->firstWhere('student_id', $student->id);
                    $effective = $config->effectiveScore(
                        $score ? (float) $score->score : 0,
                        $score ? $score->improvement_score : null,
                        (float) $activity->max_points,
                    );

                    if ($activity->activityType->is_extra) {
                        $extraPoints += $effective;
                    } else {
                        $normalPoints += $effective;
                    }
                }

                GradeBookTotal::updateOrCreate(
                    [
                        'grade_book_id' => $gradeBook->id,
                        'student_id'    => $student->id,
                    ],
                    [
                        'normal_points' => $normalPoints,
                        'extra_points'  => $extraPoints,
                        'total_points'  => $normalPoints + $extraPoints,
                    ]
                );
            }
        }

        $this->command->info('GradeBookSeeder completado con distribución de estados.');
    }
}
