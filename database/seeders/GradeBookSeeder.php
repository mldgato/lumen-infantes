<?php

namespace Database\Seeders;

use App\Models\ClassroomCourseAssignment;
use App\Models\GradeBook;
use App\Models\GradeBookActivity;
use App\Models\GradeBookScore;
use App\Models\GradeBookTotal;
use App\Models\StudentEnrollment;
use Illuminate\Database\Seeder;

class GradeBookSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Iniciando la generación de Cuadros de Notas (Unidades 1-4)...');

        $studentsByClassroom = StudentEnrollment::where('status', 'Activo')
            ->get()
            ->groupBy('classroom_id');

        $randomStatuses = ['open', 'locked', 'approved', 'rejected'];
        $totalGradeBooks = 0;
        $totalScores = 0;

        for ($unit = 1; $unit <= 4; $unit++) {
            $this->command->info("Procesando Unidad {$unit}...");

            $assignments = ClassroomCourseAssignment::where('unit', $unit)->get();

            foreach ($assignments as $assignment) {
                // Unidades 1-3 siempre aprobadas; unidad 4 aleatoria
                if ($unit <= 3) {
                    $status = 'approved';
                    $rejectionReason = null;
                } else {
                    $status = $randomStatuses[array_rand($randomStatuses)];
                    $rejectionReason = $status === 'rejected' ? 'Se requiere revisión en las notas del examen final.' : null;
                }

                $gradeBook = GradeBook::create([
                    'classroom_course_assignment_id' => $assignment->id,
                    'academic_configuration_id' => 1,
                    'status' => $status,
                    'rejection_reason' => $rejectionReason,
                ]);

                $totalGradeBooks++;

                $activities = [];
                $ordering = 1;

                // 6 Tareas/Actividades (Type 1, 10pts c/u)
                for ($i = 1; $i <= 6; $i++) {
                    $activities[] = GradeBookActivity::create([
                        'grade_book_id' => $gradeBook->id,
                        'activity_type_id' => 1,
                        'name' => "Tarea/Actividad {$i}",
                        'max_points' => 10,
                        'ordering' => $ordering++,
                    ]);
                }

                // 1 Afectivo (Type 4, 10pts)
                $activities[] = GradeBookActivity::create([
                    'grade_book_id' => $gradeBook->id,
                    'activity_type_id' => 4,
                    'name' => 'Afectivo',
                    'max_points' => 10,
                    'ordering' => $ordering++,
                ]);

                // 1 Evaluación Parcial (Type 2, 15pts)
                $activities[] = GradeBookActivity::create([
                    'grade_book_id' => $gradeBook->id,
                    'activity_type_id' => 2,
                    'name' => 'Evaluación Parcial',
                    'max_points' => 15,
                    'ordering' => $ordering++,
                ]);

                // 1 Actividad de Mejoramiento (Type 5, 10pts)
                $activities[] = GradeBookActivity::create([
                    'grade_book_id' => $gradeBook->id,
                    'activity_type_id' => 5,
                    'name' => 'Actividad de Mejoramiento',
                    'max_points' => 10,
                    'ordering' => $ordering++,
                ]);

                // 1 Examen Final (Type 3, 15pts)
                $activities[] = GradeBookActivity::create([
                    'grade_book_id' => $gradeBook->id,
                    'activity_type_id' => 3,
                    'name' => 'Examen Final',
                    'max_points' => 15,
                    'ordering' => $ordering++,
                ]);

                $classroomStudents = $studentsByClassroom->get($assignment->classroom_id, collect());
                $scoresToInsert = [];
                $totalsToInsert = [];

                foreach ($classroomStudents as $enrollment) {
                    $studentId = $enrollment->student_id;
                    $normalPoints = 0;
                    $studentScoresTemporales = [];
                    $improvementActivity = null;

                    foreach ($activities as $activity) {
                        if ($activity->activity_type_id == 5) {
                            $improvementActivity = $activity;

                            continue;
                        }

                        $score = (mt_rand(1, 4) === 1)
                            ? 0
                            : mt_rand(intval($activity->max_points / 2), intval($activity->max_points));

                        $studentScoresTemporales[] = [
                            'grade_book_activity_id' => $activity->id,
                            'student_id' => $studentId,
                            'score' => $score,
                            'improvement_score' => null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];

                        $normalPoints += $score;
                        $totalScores++;
                    }

                    if ($improvementActivity) {
                        $improvementScore = 0;

                        if ($normalPoints < 60) {
                            $improvementScore = mt_rand(intval($improvementActivity->max_points / 2), intval($improvementActivity->max_points));
                        }

                        $studentScoresTemporales[] = [
                            'grade_book_activity_id' => $improvementActivity->id,
                            'student_id' => $studentId,
                            'score' => $improvementScore,
                            'improvement_score' => null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];

                        $normalPoints += $improvementScore;
                        $totalScores++;
                    }

                    foreach ($studentScoresTemporales as $scoreData) {
                        $scoresToInsert[] = $scoreData;
                    }

                    $totalsToInsert[] = [
                        'grade_book_id' => $gradeBook->id,
                        'student_id' => $studentId,
                        'normal_points' => $normalPoints,
                        'extra_points' => 0,
                        'total_points' => $normalPoints,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if (! empty($scoresToInsert)) {
                    foreach (array_chunk($scoresToInsert, 500) as $chunk) {
                        GradeBookScore::insert($chunk);
                    }
                }

                if (! empty($totalsToInsert)) {
                    foreach (array_chunk($totalsToInsert, 500) as $chunk) {
                        GradeBookTotal::insert($chunk);
                    }
                }
            }

            $this->command->info("Unidad {$unit} completada.");
        }

        $this->command->info("¡Listo! Se generaron {$totalGradeBooks} cuadros de notas con {$totalScores} calificaciones.");
    }
}
