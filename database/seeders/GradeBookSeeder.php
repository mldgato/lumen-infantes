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
    public function run(): void
    {
        $config = AcademicConfiguration::with('activities.activityType')->first();

        if (! $config) {
            $this->command->warn('No existe configuración académica. Omitiendo GradeBookSeeder.');
            return;
        }

        // Plantilla de actividades según la configuración asignada
        $activityTemplate = [
            // activity_type_id => [nombres]
        ];

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

        foreach ($assignments as $assignment) {
            // Crear o encontrar el grade book
            $gradeBook = GradeBook::firstOrCreate(
                ['classroom_course_assignment_id' => $assignment->id],
                [
                    'academic_configuration_id' => $config->id,
                    'status'                    => 'approved',
                ]
            );

            // Si ya existe pero no está approved, actualizarlo
            if ($gradeBook->status !== 'approved') {
                $gradeBook->update(['status' => 'approved']);
            }

            // Crear actividades si el cuadro no las tiene aún
            if ($gradeBook->activities()->count() === 0) {
                $ordering = 1;
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

            // Obtener estudiantes activos del classroom
            $students = Student::whereHas(
                'enrollments',
                fn($q) =>
                $q->where('classroom_id', $assignment->classroom_id)
                    ->where('status', 'Activo')
            )->get();

            // Crear scores por actividad y estudiante
            foreach ($activities as $activity) {
                foreach ($students as $student) {
                    GradeBookScore::firstOrCreate(
                        [
                            'grade_book_activity_id' => $activity->id,
                            'student_id'             => $student->id,
                        ],
                        [
                            'score'             => fake()->randomFloat(2, (float)$activity->max_points * 0.5, (float)$activity->max_points),
                            'improvement_score' => null,
                        ]
                    );
                }
            }

            // Calcular totales por estudiante
            $allActivities = $gradeBook->activities()->with(['scores', 'activityType'])->get();

            foreach ($students as $student) {
                $normalPoints = 0;
                $extraPoints  = 0;

                foreach ($allActivities as $activity) {
                    $score = $activity->scores->firstWhere('student_id', $student->id);
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
    }
}
