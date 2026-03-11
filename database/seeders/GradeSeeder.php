<?php

namespace Database\Seeders;

use App\Models\Grade;
use Illuminate\Database\Seeder;

class GradeSeeder extends Seeder
{
    public function run(): void
    {
        $grades = [
            ['grade_name' => 'Primero Básico', 'ordering' => 1],
            ['grade_name' => 'Segundo Básico', 'ordering' => 2],
            ['grade_name' => 'Tercero Básico', 'ordering' => 3],
            ['grade_name' => 'Cuarto Bachillerato en Ciencias y Letras', 'ordering' => 4],
            ['grade_name' => 'Quinto Bachillerato en Ciencias y Letras', 'ordering' => 5],
            ['grade_name' => 'Cuarto Perito Contador', 'ordering' => 6],
            ['grade_name' => 'Quinto Perito Contador',  'ordering' => 7],
            ['grade_name' => 'Sexto Perito Contador',  'ordering' => 8],
        ];

        foreach ($grades as $grade) {
            Grade::create($grade);
        }
    }
}
