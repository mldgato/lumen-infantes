<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClassroomCourseAssignmentSeederVariant extends Seeder
{
    public function run(): void
    {
        $data = $this->expand($this->getAssignments());

        foreach (array_chunk($data, 100) as $chunk) {
            DB::table('classroom_course_assignments')->insertOrIgnore($chunk);
        }
    }

    /**
     * Cada fila: [classroom_id, professor_id, pensum_course_id, units]
     *
     * "units" es el arreglo EXACTO de unidades en que se imparte ese
     * pensum_course y debe coincidir con la columna `units` de pensum_courses.
     *
     * @return list<array{int, int, int, list<int>}>
     */
    private function getAssignments(): array
    {
        return [
            // ============================================================
            // CLASSROOM 1 — Pénsum 1 (cursos del PensumSeeder)
            // ============================================================

            // Cursos normales: 4 unidades, un profesor por curso.
            [1, 1,  1,  [1, 2, 3, 4]],
            [1, 2,  2,  [1, 2, 3, 4]],
            [1, 3,  3,  [1, 2, 3, 4]],
            [1, 4,  4,  [1, 2, 3, 4]],
            [1, 5,  5,  [1, 2, 3, 4]],

            // Curso parcial (solo 2 unidades): el mismo profesor en ambas.
            [1, 6,  6,  [1, 2]],

            // Curso padre (pensum_course 7, is_main, units = null):
            // NO se asigna directamente. Se asignan sus subcursos, cada uno
            // en su propia unidad, todos con el MISMO profesor (7).
            [1, 7,  8,  [1]],   // subcurso de la unidad 1
            [1, 7,  9,  [2]],   // subcurso de la unidad 2
            [1, 7,  10, [3]],   // subcurso de la unidad 3
            [1, 7,  11, [4]],   // subcurso de la unidad 4

            // Cursos normales restantes: 4 unidades.
            [1, 8,  12, [1, 2, 3, 4]],
            [1, 9,  13, [1, 2, 3, 4]],
            [1, 10, 14, [1, 2, 3, 4]],
        ];
    }

    /**
     * Expande cada asignación a una fila por unidad.
     *
     * @param  list<array{int, int, int, list<int>}>  $assignments
     * @return list<array{classroom_id: int, professor_id: int, pensum_course_id: int, unit: int}>
     */
    private function expand(array $assignments): array
    {
        $rows = [];

        foreach ($assignments as [$classroomId, $professorId, $pensumCourseId, $units]) {
            foreach ($units as $unit) {
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