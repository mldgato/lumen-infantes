<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClassroomCourseAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $data = $this->expand($this->getAssignments());

        foreach (array_chunk($data, 100) as $chunk) {
            DB::table('classroom_course_assignments')->insertOrIgnore($chunk);
        }
    }

    /** @return list<array{int,int,int,int}> [classroom_id, professor_id, pensum_course_id, units] */
    private function getAssignments(): array
    {
        return [
            // CLASSROOM 1 — Primero Básico Sección A (Pénsum 1, cursos 1-11)
            [1,  1,  1,  4], [1,  12, 2,  4], [1,  7,  3,  4], [1,  8,  4,  4],
            [1,  12, 5,  4], [1,  3,  6,  4], [1,  7,  7,  4], [1,  10, 8,  4],
            [1,  8,  9,  4], [1,  11, 10, 4], [1,  5,  11, 4],

            // CLASSROOM 2 — Primero Básico Sección B (Pénsum 1, cursos 1-11)
            [2,  1,  1,  4], [2,  12, 2,  4], [2,  4,  3,  4], [2,  8,  4,  4],
            [2,  12, 5,  4], [2,  3,  6,  4], [2,  9,  7,  4], [2,  10, 8,  4],
            [2,  8,  9,  4], [2,  11, 10, 4], [2,  5,  11, 4],

            // CLASSROOM 3 — Segundo Básico Sección A (Pénsum 2, cursos 12-22)
            [3,  1,  12, 4], [3,  12, 13, 4], [3,  8,  14, 4], [3,  3,  15, 4],
            [3,  6,  16, 4], [3,  3,  17, 4], [3,  9,  18, 4], [3,  10, 19, 4],
            [3,  12, 20, 4], [3,  11, 21, 4], [3,  5,  22, 4],

            // CLASSROOM 4 — Tercero Básico Sección A (Pénsum 3, cursos 23-33)
            [4,  1,  23, 4], [4,  12, 24, 4], [4,  4,  25, 4], [4,  8,  26, 4],
            [4,  11, 27, 4], [4,  3,  28, 4], [4,  9,  29, 4], [4,  10, 30, 4],
            [4,  8,  31, 4], [4,  7,  32, 4], [4,  5,  33, 4],

            // CLASSROOM 5 — Tercero Básico Sección B (Pénsum 3, cursos 23-33)
            [5,  1,  23, 4], [5,  12, 24, 4], [5,  4,  25, 4], [5,  4,  26, 4],
            [5,  11, 27, 4], [5,  3,  28, 4], [5,  9,  29, 4], [5,  10, 30, 4],
            [5,  8,  31, 4], [5,  6,  32, 4], [5,  5,  33, 4],

            // CLASSROOM 6 — Cuarto Bachillerato en Ciencias y Letras Sección A (Pénsum 4, cursos 34-43)
            [6,  3,  34, 4], [6,  12, 35, 4], [6,  5,  36, 4], [6,  4,  37, 4],
            [6,  11, 38, 4], [6,  4,  39, 4], [6,  8,  40, 4], [6,  3,  41, 4],
            [6,  2,  42, 4], [6,  6,  43, 4],

            // CLASSROOM 7 — Quinto Bachillerato en Ciencias y Letras Sección A (Pénsum 5, cursos 44-53)
            [7,  1,  44, 4], [7,  12, 45, 4], [7,  5,  46, 4], [7,  4,  47, 4],
            [7,  7,  48, 4], [7,  11, 49, 4], [7,  8,  50, 4], [7,  11, 51, 4],
            [7,  8,  52, 4], [7,  3,  53, 4],

            // CLASSROOM 8 — Cuarto Perito Contador Sección A (Pénsum 6, cursos 54-62)
            [8,  6,  54, 4], [8,  7,  55, 4], [8,  2,  56, 4], [8,  12, 57, 4],
            [8,  2,  58, 4], [8,  2,  59, 4], [8,  11, 60, 4], [8,  11, 61, 4],
            [8,  5,  62, 4],

            // CLASSROOM 9 — Quinto Perito Contador Sección A (Pénsum 7, cursos 63-71)
            [9,  6,  63, 4], [9,  7,  64, 4], [9,  12, 65, 4], [9,  6,  66, 4],
            [9,  11, 67, 4], [9,  12, 68, 4], [9,  2,  69, 4], [9,  5,  70, 4],
            [9,  5,  71, 4],

            // CLASSROOM 10 — Sexto Perito Contador Sección A (Pénsum 8, cursos 72-81, 3 unidades)
            [10, 6,  72, 3], [10, 7,  73, 3], [10, 2,  74, 3], [10, 3,  75, 3],
            [10, 3,  76, 3], [10, 2,  77, 3], [10, 12, 78, 3], [10, 2,  79, 3],
            [10, 5,  80, 3], [10, 11, 81, 3],
        ];
    }

    /** @param list<array{int,int,int,int}> $assignments */
    private function expand(array $assignments): array
    {
        $rows = [];
        foreach ($assignments as [$classroomId, $professorId, $pensumCourseId, $units]) {
            for ($unit = 1; $unit <= $units; $unit++) {
                $rows[] = [
                    'classroom_id' => $classroomId,
                    'professor_id' => $professorId,
                    'pensum_course_id' => $pensumCourseId,
                    'unit' => $unit,
                ];
            }
        }

        return $rows;
    }
}
