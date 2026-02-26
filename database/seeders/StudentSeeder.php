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
        // Creamos 20 estudiantes para tener una buena base de prueba
        Student::factory(20)->create()->each(function ($student) {

            // 1. Asignar el rol al usuario de este estudiante
            $student->user->assignRole('Estudiante');

            // 2. Crear el registro médico vinculado al usuario del estudiante
            MedicalRecord::factory()->create([
                'user_id' => $student->user_id
            ]);

            // 3. Imagen de perfil (Polimórfica)
            $student->user->image()->save(Image::factory()->make());

            // 4. Lógica de Encargados (Simulación de Núcleo Familiar)
            // Definimos los tipos permitidos
            $rolesDisponibles = ['Papá', 'Mamá', 'Encargado'];

            // Barajamos los roles y tomamos una cantidad aleatoria entre 1 y 3 
            // para probar cómo se ve el PDF con diferentes cantidades de familiares.
            $rolesAAsignar = collect($rolesDisponibles)->random(rand(1, 3));

            foreach ($rolesAAsignar as $relationship) {
                // Creamos un encargado único para este rol
                $guardian = Guardian::factory()->create();

                // 5. Vincular con la tabla pivote respetando el tipo
                $student->guardians()->attach($guardian->id, [
                    'relationship_type' => $relationship,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }
}
