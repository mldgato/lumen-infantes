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
        $professorsData = [
            ['first' => 'Verónica', 'middle' => null, 'surname' => 'Auyón', 'second' => 'Ocampo', 'married' => 'Galicia'],
            ['first' => 'María', 'middle' => 'del Carmen', 'surname' => 'Castellanos', 'second' => 'Obregón', 'married' => null],
            ['first' => 'Gloria', 'middle' => null, 'surname' => 'Gómez', 'second' => null, 'married' => null],
            ['first' => 'Mario', 'middle' => 'Luis', 'surname' => 'Rosales', 'second' => 'Argueta', 'married' => null],
            ['first' => 'Tomás', 'middle' => 'Francisco', 'surname' => 'Xon', 'second' => null, 'married' => null],
            ['first' => 'Doroteo', 'middle' => null, 'surname' => 'Ajin', 'second' => 'Monroy', 'married' => null],
            ['first' => 'Vicente', 'middle' => null, 'surname' => 'Culajay', 'second' => null, 'married' => null],
            ['first' => 'José', 'middle' => null, 'surname' => 'Gutierrez', 'second' => null, 'married' => null],
            ['first' => 'Julio', 'middle' => null, 'surname' => 'Vivar', 'second' => null, 'married' => null],
            ['first' => 'Manuel', 'middle' => null, 'surname' => 'García', 'second' => 'Pineda', 'married' => null],
            ['first' => 'Edna', 'middle' => 'Marina', 'surname' => 'Castañaza', 'second' => null, 'married' => 'Cerrate'],
            ['first' => 'Laura', 'middle' => 'Lily', 'surname' => 'Larios', 'second' => 'Subuyuj', 'married' => null],
        ];

        foreach ($professorsData as $index => $data) {
            $i = $index + 1;

            // Crear el usuario con los nombres desglosados
            $user = User::factory()->create([
                'first_name'      => $data['first'],
                'middle_name'     => $data['middle'],
                'surname'         => $data['surname'],
                'second_surname'  => $data['second'],
                'married_surname' => $data['married'],
                'email'           => "teacher{$i}@lumen.net",
            ]);

            // Crear el profesor vinculado
            $professor = Professor::factory()->create([
                'user_id' => $user->id,
            ]);

            // Asignar el rol
            $user->assignRole('Profesor');

            // Crear imagen polimórfica
            $user->image()->save(Image::factory()->make());

            // Crear el registro médico
            MedicalRecord::factory()->create([
                'user_id' => $user->id,
            ]);
        }
    }
}
