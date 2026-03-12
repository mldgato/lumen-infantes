<?php

namespace Database\Seeders;

use App\Models\MedicalRecord;
use App\Models\Professor;
use App\Models\Image;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProfessorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Creamos 10 profesores
        for ($i = 1; $i <= 11; $i++) {

            // Creamos el profesor sobreescribiendo el email del User asociado
            $professor = Professor::factory()->create([
                'user_id' => User::factory()->create([
                    'email' => "teacher{$i}@lumen.net"
                ])->id,
            ]);

            // Asignar el rol al usuario de este profesor
            $professor->user->assignRole('Profesor');

            // Crear imagen polimórfica
            $professor->user->image()->save(Image::factory()->make());

            // Crear el registro médico vinculado al usuario del profesor
            MedicalRecord::factory()->create([
                'user_id' => $professor->user_id
            ]);
        }
    }
}
