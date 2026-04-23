<?php

namespace Database\Seeders;

use App\Models\MedicalRecord;
use App\Models\Student;
use App\Models\Image;
use App\Models\User;
use Illuminate\Database\Seeder;
use App\Models\StudentEnrollment;
use Faker\Factory as Faker;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('es_ES'); // Localizado para nombres en español

        // Mantenemos las llaves de los grupos para respetar las aulas existentes
        $estudiantesPorGrupo = [
            1 => 24, // Aula 1: 24 alumnos
            2 => 20, // Aula 2: 20 alumnos
            3 => 35, // ... y así sucesivamente
            4 => 20,
            5 => 20,
            8 => 17,
            9 => 17,
            10 => 13,
            7 => 13,
            6 => 16,
        ];

        $contadorGlobal = 1;

        foreach ($estudiantesPorGrupo as $classroomId => $cantidad) {
            $this->command->info("Procesando Aula ID: {$classroomId} ({$cantidad} alumnos)");

            for ($i = 0; $i < $cantidad; $i++) {
                $gender = $faker->randomElement(['Masculino', 'Femenino']);
                $firstName = ($gender == 'Masculino') ? $faker->firstNameMale : $faker->firstNameFemale;

                $user = User::factory()->create([
                    'first_name'      => $firstName,
                    'middle_name'     => $faker->firstName,
                    'surname'         => $faker->lastName,
                    'second_surname'  => $faker->lastName,
                    'married_surname' => null,
                    'birthdate'       => $faker->date('Y-m-d', '-12 years'),
                    'cui'             => $faker->numerify('#############'), // 13 dígitos
                    'gender'          => $gender,
                    'email'           => "student{$contadorGlobal}@lumen.net",
                ]);

                $student = Student::factory()->create([
                    'user_id'         => $user->id,
                    'personal_code'   => strtoupper($faker->bothify('?###???')),
                    'carne'           => strtoupper($faker->bothify('?###???')),
                    'is_own_guardian' => false,
                ]);

                $user->assignRole('Estudiante');
                $user->image()->save(Image::factory()->make());

                MedicalRecord::create(['user_id' => $user->id]);

                StudentEnrollment::create([
                    'student_id'   => $student->id,
                    'classroom_id' => $classroomId,
                    'status'       => 'Activo',
                ]);

                $contadorGlobal++;
            }
        }

        $this->command->info("¡Carga masiva finalizada! Se procesaron " . ($contadorGlobal - 1) . " alumnos aleatorios.");
    }
}
