<?php

namespace Database\Seeders;

use App\Models\MedicalRecord;
use App\Models\Professor;
use App\Models\Image;
use Illuminate\Database\Seeder;

class ProfessorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crea 5 profesores. El Factory de Professor crea automáticamente su User asociado.
        Professor::factory(10)->create()->each(function ($professor) {

            // Asignar el rol al usuario de este profesor
            $professor->user->assignRole('Profesor');

            $professor->user->image()->save(Image::factory()->make());

            // Crear el registro médico vinculado al usuario del profesor
            MedicalRecord::factory()->create([
                'user_id' => $professor->user_id
            ]);
        });
    }
}
