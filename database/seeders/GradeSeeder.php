<?php

namespace Database\Seeders;

use App\Models\Grade;
use Illuminate\Database\Seeder;

class GradeSeeder extends Seeder
{
    public function run(): void
    {
        $grades = [
            ['grade_name' => 'Prekinder', 'ordering' => 1],
            ['grade_name' => 'Kinder', 'ordering' => 2],
            ['grade_name' => 'Preparatoria', 'ordering' => 3],

            ['grade_name' => 'Primero Primaria', 'ordering' => 4],
            ['grade_name' => 'Segundo Primaria', 'ordering' => 5],
            ['grade_name' => 'Tercero Primaria', 'ordering' => 6],
            ['grade_name' => 'Cuarto Primaria',  'ordering' => 7],
            ['grade_name' => 'Quinto Primaria',  'ordering' => 8],
            ['grade_name' => 'Sexto Primaria',  'ordering' => 9],

            ['grade_name' => 'Primero Básico', 'ordering' => 10],
            ['grade_name' => 'Segundo Básico', 'ordering' => 11],
            ['grade_name' => 'Tercero Básico', 'ordering' => 12],

            ['grade_name' => 'Cuarto Bachillerato en Ciencias y Letras Con Orientación en Computación', 'ordering' => 13],
            ['grade_name' => 'Cuarto Bachillerato en Ciencias y Letras', 'ordering' => 14],
            ['grade_name' => 'Cuarto Perito Contador', 'ordering' => 15],

            ['grade_name' => 'Quinto Bachillerato en Ciencias y Letras Con Orientación en Computación', 'ordering' => 16],
            ['grade_name' => 'Quinto Bachillerato en Ciencias y Letras', 'ordering' => 17],
            ['grade_name' => 'Quinto Perito Contador',  'ordering' => 18],

            ['grade_name' => 'Sexto Perito Contador',  'ordering' => 19],
        ];

        foreach ($grades as $grade) {
            Grade::create($grade);
        }
    }
}
