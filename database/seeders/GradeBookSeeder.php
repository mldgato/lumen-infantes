<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ClassroomCourseAssignment;
use App\Models\GradeBook;
use App\Models\GradeBookActivity;
use App\Models\GradeBookScore;
use App\Models\GradeBookTotal;
use App\Models\StudentEnrollment;

class GradeBookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Iniciando la generación de Cuadros de Notas para la Unidad 1...');

        // 1. Obtener ÚNICAMENTE las asignaciones correspondientes a la Unidad 1
        $assignments = ClassroomCourseAssignment::where('unit', 1)->get();
        $statuses = ['open', 'locked', 'approved', 'rejected'];

        // Almacenar todos los estudiantes activos agrupados por aula para no repetir consultas
        $studentsByClassroom = StudentEnrollment::where('status', 'Activo')
            ->get()
            ->groupBy('classroom_id');

        $totalGradeBooks = 0;
        $totalScores = 0;

        foreach ($assignments as $assignment) {
            // Generar un estado aleatorio para el cuadro
            $status = $statuses[array_rand($statuses)];
            $rejectionReason = $status === 'rejected' ? 'Se requiere revisión en las notas del examen final.' : null;

            // 2. Crear el GradeBook (Cuadro de notas)
            $gradeBook = GradeBook::create([
                'classroom_course_assignment_id' => $assignment->id,
                'academic_configuration_id'      => 1, // ID configurado
                'status'                         => $status,
                'rejection_reason'               => $rejectionReason,
            ]);

            $totalGradeBooks++;

            // 3. Crear las Actividades según el AcademicConfiguration ID 1
            $activities = [];
            $ordering = 1;

            // a. 6 Tareas/Actividades (Type 1, 10pts c/u)
            for ($i = 1; $i <= 6; $i++) {
                $activities[] = GradeBookActivity::create([
                    'grade_book_id'    => $gradeBook->id,
                    'activity_type_id' => 1,
                    'name'             => "Tarea/Actividad {$i}",
                    'max_points'       => 10,
                    'ordering'         => $ordering++,
                ]);
            }

            // b. 1 Afectivo (Type 4, 10pts)
            $activities[] = GradeBookActivity::create([
                'grade_book_id'    => $gradeBook->id,
                'activity_type_id' => 4,
                'name'             => "Afectivo",
                'max_points'       => 10,
                'ordering'         => $ordering++,
            ]);

            // c. NUEVA: 1 Evaluación/Actividad (Type 2, 15pts)
            $activities[] = GradeBookActivity::create([
                'grade_book_id'    => $gradeBook->id,
                'activity_type_id' => 2,
                'name'             => "Evaluación Parcial", // Puedes cambiar este nombre
                'max_points'       => 15,
                'ordering'         => $ordering++,
            ]);

            // d. 1 Actividad de Mejoramiento (Type 5, 10pts)
            $activities[] = GradeBookActivity::create([
                'grade_book_id'    => $gradeBook->id,
                'activity_type_id' => 5,
                'name'             => "Actividad de Mejoramiento",
                'max_points'       => 10,
                'ordering'         => $ordering++,
            ]);

            // e. 1 Examen Final (Type 3, ajustado a 15pts)
            $activities[] = GradeBookActivity::create([
                'grade_book_id'    => $gradeBook->id,
                'activity_type_id' => 3,
                'name'             => "Examen Final",
                'max_points'       => 15,
                'ordering'         => $ordering++,
            ]);

            // 4. Llenar notas y totales para los estudiantes de esta aula
            $classroomStudents = $studentsByClassroom->get($assignment->classroom_id, collect());

            $scoresToInsert = [];
            $totalsToInsert = [];

            foreach ($classroomStudents as $enrollment) {
                $studentId = $enrollment->student_id;
                $normalPoints = 0;
                $studentScoresTemporales = [];
                $improvementActivity = null;

                // Paso A: Evaluar todas las actividades MENOS la de mejoramiento
                foreach ($activities as $activity) {
                    if ($activity->activity_type_id == 5) {
                        $improvementActivity = $activity;
                        continue; // Saltamos el mejoramiento por ahora
                    }

                    // Generar una nota aleatoria realista en enteros (25% probabilidad de estar en cero)
                    $score = (mt_rand(1, 4) === 1)
                        ? 0
                        : mt_rand(intval($activity->max_points / 2), intval($activity->max_points));

                    $studentScoresTemporales[] = [
                        'grade_book_activity_id' => $activity->id,
                        'student_id'             => $studentId,
                        'score'                  => $score,
                        'improvement_score'      => null,
                        'created_at'             => now(),
                        'updated_at'             => now(),
                    ];

                    $normalPoints += $score;
                    $totalScores++;
                }

                // Paso B: Evaluar la actividad de Mejoramiento según la nota acumulada
                if ($improvementActivity) {
                    $improvementScore = 0;

                    // Si sumó menos de 60, se le califica esta actividad con enteros
                    if ($normalPoints < 60) {
                        $improvementScore = mt_rand(intval($improvementActivity->max_points / 2), intval($improvementActivity->max_points));
                    }

                    $studentScoresTemporales[] = [
                        'grade_book_activity_id' => $improvementActivity->id,
                        'student_id'             => $studentId,
                        'score'                  => $improvementScore,
                        'improvement_score'      => null,
                        'created_at'             => now(),
                        'updated_at'             => now(),
                    ];

                    // Sumamos los puntos de mejoramiento al total (depende de la regla de tu colegio, lo sumo por defecto)
                    $normalPoints += $improvementScore;
                    $totalScores++;
                }

                // Paso C: Pasar las notas temporales al arreglo de inserción masiva
                foreach ($studentScoresTemporales as $scoreData) {
                    $scoresToInsert[] = $scoreData;
                }

                // Generar el total del estudiante
                $totalsToInsert[] = [
                    'grade_book_id' => $gradeBook->id,
                    'student_id'    => $studentId,
                    'normal_points' => $normalPoints,
                    'extra_points'  => 0,
                    'total_points'  => $normalPoints,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
            }

            // Inserción masiva por cada GradeBook para optimizar velocidad
            if (!empty($scoresToInsert)) {
                foreach (array_chunk($scoresToInsert, 500) as $chunk) {
                    GradeBookScore::insert($chunk);
                }
            }

            if (!empty($totalsToInsert)) {
                foreach (array_chunk($totalsToInsert, 500) as $chunk) {
                    GradeBookTotal::insert($chunk);
                }
            }
        }

        $this->command->info("¡Listo! Se generaron {$totalGradeBooks} cuadros de notas con {$totalScores} calificaciones enteras para la Unidad 1.");
    }
}
