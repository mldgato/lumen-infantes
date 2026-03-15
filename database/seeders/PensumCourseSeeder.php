<?php

namespace Database\Seeders;

use App\Models\PensumCourse;
use Illuminate\Database\Seeder;

class PensumCourseSeeder extends Seeder
{
    public function run(): void
    {
        // Definimos de forma limpia qué cursos pertenecen a qué pensum.
        // El orden en que coloques los cursos aquí, definirá automáticamente su 'ordering'.
        $pensums = [
            // Pensums 1, 2 y 3 (Ya incluyen el curso 6)
            1 => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
            2 => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
            3 => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],

            // Pensum 4 (Ordenado lógicamente para que el índice coincida con el ordering 1 al 10)
            4 => [15, 16, 17, 3, 20, 22, 8, 26, 27, 28],

            // Pensum 5
            5 => [15, 16, 18, 3, 19, 21, 23, 24, 25, 29],

            // Pensum 6
            6 => [30, 31, 32, 33, 34, 35, 36, 37, 38, 58],

            // Pensum 7
            7 => [39, 40, 41, 42, 43, 44, 45, 46, 47, 60],

            // Pensum 8 (Este solo tiene 3 unidades)
            8 => [48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 60],
        ];

        foreach ($pensums as $pensumId => $courses) {
            // El pensum 8 tiene 3 unidades, el resto tiene 4
            $units = ($pensumId === 8) ? [1, 2, 3] : [1, 2, 3, 4];

            foreach ($courses as $index => $courseId) {
                PensumCourse::updateOrCreate(
                    // Condiciones para buscar el registro (Evita duplicados sin usar ID)
                    [
                        'pensum_id' => $pensumId,
                        'course_id' => $courseId,
                    ],
                    // Datos a actualizar o crear
                    [
                        'parent_id'   => null,
                        'units'       => $units,
                        'is_main'     => 0,
                        'is_official' => 1,
                        'ordering'    => $index + 1, // Genera el ordering automáticamente (1, 2, 3...)
                    ]
                );
            }
        }
    }
}
