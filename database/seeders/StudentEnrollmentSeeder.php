<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Illuminate\Database\Seeder;

class StudentEnrollmentSeeder extends Seeder
{
    public function run(): void
    {
        $classrooms = Classroom::all();

        // Obtenemos todos los estudiantes sin inscripción aún y los barajamos
        $students = Student::whereDoesntHave('enrollments')->get()->shuffle();

        $studentIndex = 0;

        foreach ($classrooms as $classroom) {
            for ($i = 0; $i < 15; $i++) {
                if ($studentIndex >= $students->count()) {
                    $this->command->warn('No hay suficientes estudiantes sin inscripción.');
                    return;
                }

                StudentEnrollment::create([
                    'student_id'   => $students[$studentIndex]->id,
                    'classroom_id' => $classroom->id,
                    'status'       => 'Activo',
                ]);

                $studentIndex++;
            }
        }

        $this->command->info("Se crearon {$studentIndex} inscripciones correctamente.");
    }
}
