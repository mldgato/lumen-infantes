<?php

namespace Database\Seeders;

use App\Models\Pensum;
use App\Models\PensumCourse;
use Illuminate\Database\Seeder;

class PensumSeederVariant extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ----------------------------------------------------------------
        // Pensum
        // unit_percentages se pasa como ARREGLO PHP porque el modelo Pensum
        // castea esa columna a 'array'. Pasar un string causa doble
        // codificación JSON y rompe count()/foreach en las vistas.
        // ----------------------------------------------------------------
        $pensum = Pensum::create([
            'grade_id' => 1,
            'year' => '2026',
            'units' => 4,
            'unit_percentages' => [25, 25, 25, 25],
        ]);

        // ----------------------------------------------------------------
        // Pensum courses
        //
        // 'orig_id'     => id original del JSON (solo para resolver parent_id)
        // 'parent_orig' => id original del padre (null si no tiene)
        //
        // El orden respeta la secuencia de inserción original: el curso
        // principal (orig_id 7) se crea antes que sus hijos (8-11), por lo
        // que su id ya está disponible en el mapeo.
        //
        // 'units' se pasa como ARREGLO PHP (el modelo PensumCourse castea a
        // 'array'). El curso principal usa null porque solo es contenedor.
        // ----------------------------------------------------------------
        $rows = [
            ['orig_id' => 1,  'course_id' => 1,  'parent_orig' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 1],
            ['orig_id' => 2,  'course_id' => 2,  'parent_orig' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 2],
            ['orig_id' => 3,  'course_id' => 3,  'parent_orig' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 3],
            ['orig_id' => 4,  'course_id' => 4,  'parent_orig' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 4],
            ['orig_id' => 5,  'course_id' => 5,  'parent_orig' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 5],
            ['orig_id' => 6,  'course_id' => 6,  'parent_orig' => null, 'units' => [1, 2],       'is_main' => 0, 'is_official' => 1, 'ordering' => 6],
            ['orig_id' => 7,  'course_id' => 7,  'parent_orig' => null, 'units' => null,          'is_main' => 1, 'is_official' => 1, 'ordering' => 7],
            ['orig_id' => 8,  'course_id' => 14, 'parent_orig' => 7,    'units' => [1],           'is_main' => 0, 'is_official' => 1, 'ordering' => 0],
            ['orig_id' => 9,  'course_id' => 13, 'parent_orig' => 7,    'units' => [2],           'is_main' => 0, 'is_official' => 1, 'ordering' => 0],
            ['orig_id' => 10, 'course_id' => 11, 'parent_orig' => 7,    'units' => [3],           'is_main' => 0, 'is_official' => 1, 'ordering' => 0],
            ['orig_id' => 11, 'course_id' => 12, 'parent_orig' => 7,    'units' => [4],           'is_main' => 0, 'is_official' => 1, 'ordering' => 0],
            ['orig_id' => 12, 'course_id' => 8,  'parent_orig' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 8],
            ['orig_id' => 13, 'course_id' => 9,  'parent_orig' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 9],
            ['orig_id' => 14, 'course_id' => 10, 'parent_orig' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 10],
        ];

        $map = [];

        foreach ($rows as $row) {
            $created = PensumCourse::create([
                'pensum_id' => $pensum->id,
                'course_id' => $row['course_id'],
                'parent_id' => $row['parent_orig'] !== null ? $map[$row['parent_orig']] : null,
                'units' => $row['units'],
                'is_main' => $row['is_main'],
                'is_official' => $row['is_official'],
                'ordering' => $row['ordering'],
            ]);

            $map[$row['orig_id']] = $created->id;
        }
    }
}
