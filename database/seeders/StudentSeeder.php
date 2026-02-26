<?php

namespace Database\Seeders;

use App\Models\Guardian;
use App\Models\MedicalRecord;
use App\Models\Student;
use App\Models\Image;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crea 10 estudiantes. El Factory de Student ya crea automáticamente el User asociado.
        Student::factory(20)->create()->each(function ($student) {

            // 1. Asignar el rol al usuario de este estudiante
            $student->user->assignRole('Estudiante');

            // 2. Crear el registro médico vinculado al usuario del estudiante
            MedicalRecord::factory()->create([
                'user_id' => $student->user_id
            ]);

            //3.  Usamos make() en lugar de create() para que la relación polimórfica llene los campos de ID y Type por nosotros
            $student->user->image()->save(Image::factory()->make());

            // 4. Crear un encargado (padre, madre o tutor)
            $guardian = Guardian::factory()->create();

            // 5. Vincular el encargado con el estudiante en la tabla pivote
            $relationship = collect(['padre', 'madre', 'encargado'])->random();
            $student->guardians()->attach($guardian->id, [
                'relationship_type' => $relationship
            ]);
        });
    }
}
