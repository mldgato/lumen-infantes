<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ClassroomCourseAssignment;

class ClassroomCourseAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            // Classroom ID: 1
            ['id' => 1, 'classroom_id' => 1, 'professor_id' => 3, 'pensum_course_id' => 1, 'unit' => 1, 'created_at' => '2026-03-12 02:53:09', 'updated_at' => '2026-03-12 02:53:09'],
            ['id' => 2, 'classroom_id' => 1, 'professor_id' => 3, 'pensum_course_id' => 1, 'unit' => 2, 'created_at' => '2026-03-12 02:53:09', 'updated_at' => '2026-03-12 02:53:09'],
            ['id' => 3, 'classroom_id' => 1, 'professor_id' => 3, 'pensum_course_id' => 1, 'unit' => 3, 'created_at' => '2026-03-12 02:53:09', 'updated_at' => '2026-03-12 02:53:09'],
            ['id' => 4, 'classroom_id' => 1, 'professor_id' => 3, 'pensum_course_id' => 1, 'unit' => 4, 'created_at' => '2026-03-12 02:53:09', 'updated_at' => '2026-03-12 02:53:09'],
            ['id' => 5, 'classroom_id' => 1, 'professor_id' => 8, 'pensum_course_id' => 2, 'unit' => 1, 'created_at' => '2026-03-12 02:53:09', 'updated_at' => '2026-03-12 02:53:09'],
            ['id' => 6, 'classroom_id' => 1, 'professor_id' => 8, 'pensum_course_id' => 2, 'unit' => 2, 'created_at' => '2026-03-12 02:53:09', 'updated_at' => '2026-03-12 02:53:09'],
            ['id' => 7, 'classroom_id' => 1, 'professor_id' => 8, 'pensum_course_id' => 2, 'unit' => 3, 'created_at' => '2026-03-12 02:53:09', 'updated_at' => '2026-03-12 02:53:09'],
            ['id' => 8, 'classroom_id' => 1, 'professor_id' => 8, 'pensum_course_id' => 2, 'unit' => 4, 'created_at' => '2026-03-12 02:53:09', 'updated_at' => '2026-03-12 02:53:09'],
            ['id' => 9, 'classroom_id' => 1, 'professor_id' => 5, 'pensum_course_id' => 3, 'unit' => 1, 'created_at' => '2026-03-12 02:53:09', 'updated_at' => '2026-03-12 02:53:09'],
            ['id' => 10, 'classroom_id' => 1, 'professor_id' => 5, 'pensum_course_id' => 3, 'unit' => 2, 'created_at' => '2026-03-12 02:53:09', 'updated_at' => '2026-03-12 02:53:09'],
            ['id' => 11, 'classroom_id' => 1, 'professor_id' => 5, 'pensum_course_id' => 3, 'unit' => 3, 'created_at' => '2026-03-12 02:53:09', 'updated_at' => '2026-03-12 02:53:09'],
            ['id' => 12, 'classroom_id' => 1, 'professor_id' => 5, 'pensum_course_id' => 3, 'unit' => 4, 'created_at' => '2026-03-12 02:53:09', 'updated_at' => '2026-03-12 02:53:09'],
            ['id' => 13, 'classroom_id' => 1, 'professor_id' => 9, 'pensum_course_id' => 4, 'unit' => 1, 'created_at' => '2026-03-12 02:53:09', 'updated_at' => '2026-03-12 02:53:09'],
            ['id' => 14, 'classroom_id' => 1, 'professor_id' => 9, 'pensum_course_id' => 4, 'unit' => 2, 'created_at' => '2026-03-12 02:53:09', 'updated_at' => '2026-03-12 02:53:09'],
            ['id' => 15, 'classroom_id' => 1, 'professor_id' => 9, 'pensum_course_id' => 4, 'unit' => 3, 'created_at' => '2026-03-12 02:53:09', 'updated_at' => '2026-03-12 02:53:09'],
            ['id' => 16, 'classroom_id' => 1, 'professor_id' => 9, 'pensum_course_id' => 4, 'unit' => 4, 'created_at' => '2026-03-12 02:53:09', 'updated_at' => '2026-03-12 02:53:09'],
            ['id' => 17, 'classroom_id' => 1, 'professor_id' => 4, 'pensum_course_id' => 5, 'unit' => 1, 'created_at' => '2026-03-12 02:53:09', 'updated_at' => '2026-03-12 02:53:09'],
            ['id' => 18, 'classroom_id' => 1, 'professor_id' => 4, 'pensum_course_id' => 5, 'unit' => 2, 'created_at' => '2026-03-12 02:53:09', 'updated_at' => '2026-03-12 02:53:09'],
            ['id' => 19, 'classroom_id' => 1, 'professor_id' => 4, 'pensum_course_id' => 5, 'unit' => 3, 'created_at' => '2026-03-12 02:53:09', 'updated_at' => '2026-03-12 02:53:09'],
            ['id' => 20, 'classroom_id' => 1, 'professor_id' => 4, 'pensum_course_id' => 5, 'unit' => 4, 'created_at' => '2026-03-12 02:53:09', 'updated_at' => '2026-03-12 02:53:09'],
            ['id' => 21, 'classroom_id' => 1, 'professor_id' => 1, 'pensum_course_id' => 6, 'unit' => 1, 'created_at' => '2026-03-12 02:53:09', 'updated_at' => '2026-03-12 02:53:09'],
            ['id' => 22, 'classroom_id' => 1, 'professor_id' => 1, 'pensum_course_id' => 6, 'unit' => 2, 'created_at' => '2026-03-12 02:53:09', 'updated_at' => '2026-03-12 02:53:09'],
            ['id' => 23, 'classroom_id' => 1, 'professor_id' => 2, 'pensum_course_id' => 8, 'unit' => 1, 'created_at' => '2026-03-12 02:53:09', 'updated_at' => '2026-03-12 02:53:09'],
            ['id' => 24, 'classroom_id' => 1, 'professor_id' => 2, 'pensum_course_id' => 9, 'unit' => 2, 'created_at' => '2026-03-12 02:53:09', 'updated_at' => '2026-03-12 02:53:09'],
            ['id' => 25, 'classroom_id' => 1, 'professor_id' => 2, 'pensum_course_id' => 10, 'unit' => 3, 'created_at' => '2026-03-12 02:53:09', 'updated_at' => '2026-03-12 02:53:09'],
            ['id' => 26, 'classroom_id' => 1, 'professor_id' => 2, 'pensum_course_id' => 11, 'unit' => 4, 'created_at' => '2026-03-12 02:53:09', 'updated_at' => '2026-03-12 02:53:09'],
            ['id' => 27, 'classroom_id' => 1, 'professor_id' => 6, 'pensum_course_id' => 12, 'unit' => 1, 'created_at' => '2026-03-12 02:53:09', 'updated_at' => '2026-03-12 02:53:09'],
            ['id' => 28, 'classroom_id' => 1, 'professor_id' => 6, 'pensum_course_id' => 12, 'unit' => 2, 'created_at' => '2026-03-12 02:53:09', 'updated_at' => '2026-03-12 02:53:09'],
            ['id' => 29, 'classroom_id' => 1, 'professor_id' => 6, 'pensum_course_id' => 12, 'unit' => 3, 'created_at' => '2026-03-12 02:53:09', 'updated_at' => '2026-03-12 02:53:09'],
            ['id' => 30, 'classroom_id' => 1, 'professor_id' => 6, 'pensum_course_id' => 12, 'unit' => 4, 'created_at' => '2026-03-12 02:53:09', 'updated_at' => '2026-03-12 02:53:09'],
            ['id' => 31, 'classroom_id' => 1, 'professor_id' => 10, 'pensum_course_id' => 13, 'unit' => 1, 'created_at' => '2026-03-12 02:53:09', 'updated_at' => '2026-03-12 02:53:09'],
            ['id' => 32, 'classroom_id' => 1, 'professor_id' => 10, 'pensum_course_id' => 13, 'unit' => 2, 'created_at' => '2026-03-12 02:53:09', 'updated_at' => '2026-03-12 02:53:09'],
            ['id' => 33, 'classroom_id' => 1, 'professor_id' => 10, 'pensum_course_id' => 13, 'unit' => 3, 'created_at' => '2026-03-12 02:53:09', 'updated_at' => '2026-03-12 02:53:09'],
            ['id' => 34, 'classroom_id' => 1, 'professor_id' => 10, 'pensum_course_id' => 13, 'unit' => 4, 'created_at' => '2026-03-12 02:53:09', 'updated_at' => '2026-03-12 02:53:09'],
            ['id' => 35, 'classroom_id' => 1, 'professor_id' => 7, 'pensum_course_id' => 14, 'unit' => 1, 'created_at' => '2026-03-12 02:53:09', 'updated_at' => '2026-03-12 02:53:09'],
            ['id' => 36, 'classroom_id' => 1, 'professor_id' => 7, 'pensum_course_id' => 14, 'unit' => 2, 'created_at' => '2026-03-12 02:53:09', 'updated_at' => '2026-03-12 02:53:09'],
            ['id' => 37, 'classroom_id' => 1, 'professor_id' => 7, 'pensum_course_id' => 14, 'unit' => 3, 'created_at' => '2026-03-12 02:53:09', 'updated_at' => '2026-03-12 02:53:09'],
            ['id' => 38, 'classroom_id' => 1, 'professor_id' => 7, 'pensum_course_id' => 14, 'unit' => 4, 'created_at' => '2026-03-12 02:53:09', 'updated_at' => '2026-03-12 02:53:09'],

            // Classroom ID: 2
            ['id' => 39, 'classroom_id' => 2, 'professor_id' => 3, 'pensum_course_id' => 1, 'unit' => 1, 'created_at' => '2026-03-12 02:53:18', 'updated_at' => '2026-03-12 02:53:18'],
            ['id' => 40, 'classroom_id' => 2, 'professor_id' => 3, 'pensum_course_id' => 1, 'unit' => 2, 'created_at' => '2026-03-12 02:53:18', 'updated_at' => '2026-03-12 02:53:18'],
            ['id' => 41, 'classroom_id' => 2, 'professor_id' => 3, 'pensum_course_id' => 1, 'unit' => 3, 'created_at' => '2026-03-12 02:53:18', 'updated_at' => '2026-03-12 02:53:18'],
            ['id' => 42, 'classroom_id' => 2, 'professor_id' => 3, 'pensum_course_id' => 1, 'unit' => 4, 'created_at' => '2026-03-12 02:53:18', 'updated_at' => '2026-03-12 02:53:18'],
            ['id' => 43, 'classroom_id' => 2, 'professor_id' => 8, 'pensum_course_id' => 2, 'unit' => 1, 'created_at' => '2026-03-12 02:53:18', 'updated_at' => '2026-03-12 02:53:18'],
            ['id' => 44, 'classroom_id' => 2, 'professor_id' => 8, 'pensum_course_id' => 2, 'unit' => 2, 'created_at' => '2026-03-12 02:53:18', 'updated_at' => '2026-03-12 02:53:18'],
            ['id' => 45, 'classroom_id' => 2, 'professor_id' => 8, 'pensum_course_id' => 2, 'unit' => 3, 'created_at' => '2026-03-12 02:53:18', 'updated_at' => '2026-03-12 02:53:18'],
            ['id' => 46, 'classroom_id' => 2, 'professor_id' => 8, 'pensum_course_id' => 2, 'unit' => 4, 'created_at' => '2026-03-12 02:53:18', 'updated_at' => '2026-03-12 02:53:18'],
            ['id' => 47, 'classroom_id' => 2, 'professor_id' => 5, 'pensum_course_id' => 3, 'unit' => 1, 'created_at' => '2026-03-12 02:53:18', 'updated_at' => '2026-03-12 02:53:18'],
            ['id' => 48, 'classroom_id' => 2, 'professor_id' => 5, 'pensum_course_id' => 3, 'unit' => 2, 'created_at' => '2026-03-12 02:53:18', 'updated_at' => '2026-03-12 02:53:18'],
            ['id' => 49, 'classroom_id' => 2, 'professor_id' => 5, 'pensum_course_id' => 3, 'unit' => 3, 'created_at' => '2026-03-12 02:53:18', 'updated_at' => '2026-03-12 02:53:18'],
            ['id' => 50, 'classroom_id' => 2, 'professor_id' => 5, 'pensum_course_id' => 3, 'unit' => 4, 'created_at' => '2026-03-12 02:53:18', 'updated_at' => '2026-03-12 02:53:18'],
            ['id' => 51, 'classroom_id' => 2, 'professor_id' => 9, 'pensum_course_id' => 4, 'unit' => 1, 'created_at' => '2026-03-12 02:53:18', 'updated_at' => '2026-03-12 02:53:18'],
            ['id' => 52, 'classroom_id' => 2, 'professor_id' => 9, 'pensum_course_id' => 4, 'unit' => 2, 'created_at' => '2026-03-12 02:53:18', 'updated_at' => '2026-03-12 02:53:18'],
            ['id' => 53, 'classroom_id' => 2, 'professor_id' => 9, 'pensum_course_id' => 4, 'unit' => 3, 'created_at' => '2026-03-12 02:53:18', 'updated_at' => '2026-03-12 02:53:18'],
            ['id' => 54, 'classroom_id' => 2, 'professor_id' => 9, 'pensum_course_id' => 4, 'unit' => 4, 'created_at' => '2026-03-12 02:53:18', 'updated_at' => '2026-03-12 02:53:18'],
            ['id' => 55, 'classroom_id' => 2, 'professor_id' => 4, 'pensum_course_id' => 5, 'unit' => 1, 'created_at' => '2026-03-12 02:53:18', 'updated_at' => '2026-03-12 02:53:18'],
            ['id' => 56, 'classroom_id' => 2, 'professor_id' => 4, 'pensum_course_id' => 5, 'unit' => 2, 'created_at' => '2026-03-12 02:53:18', 'updated_at' => '2026-03-12 02:53:18'],
            ['id' => 57, 'classroom_id' => 2, 'professor_id' => 4, 'pensum_course_id' => 5, 'unit' => 3, 'created_at' => '2026-03-12 02:53:18', 'updated_at' => '2026-03-12 02:53:18'],
            ['id' => 58, 'classroom_id' => 2, 'professor_id' => 4, 'pensum_course_id' => 5, 'unit' => 4, 'created_at' => '2026-03-12 02:53:18', 'updated_at' => '2026-03-12 02:53:18'],
            ['id' => 59, 'classroom_id' => 2, 'professor_id' => 1, 'pensum_course_id' => 6, 'unit' => 1, 'created_at' => '2026-03-12 02:53:18', 'updated_at' => '2026-03-12 02:53:18'],
            ['id' => 60, 'classroom_id' => 2, 'professor_id' => 1, 'pensum_course_id' => 6, 'unit' => 2, 'created_at' => '2026-03-12 02:53:19', 'updated_at' => '2026-03-12 02:53:19'],
            ['id' => 61, 'classroom_id' => 2, 'professor_id' => 2, 'pensum_course_id' => 8, 'unit' => 1, 'created_at' => '2026-03-12 02:53:19', 'updated_at' => '2026-03-12 02:53:19'],
            ['id' => 62, 'classroom_id' => 2, 'professor_id' => 2, 'pensum_course_id' => 9, 'unit' => 2, 'created_at' => '2026-03-12 02:53:19', 'updated_at' => '2026-03-12 02:53:19'],
            ['id' => 63, 'classroom_id' => 2, 'professor_id' => 2, 'pensum_course_id' => 10, 'unit' => 3, 'created_at' => '2026-03-12 02:53:19', 'updated_at' => '2026-03-12 02:53:19'],
            ['id' => 64, 'classroom_id' => 2, 'professor_id' => 2, 'pensum_course_id' => 11, 'unit' => 4, 'created_at' => '2026-03-12 02:53:19', 'updated_at' => '2026-03-12 02:53:19'],
            ['id' => 65, 'classroom_id' => 2, 'professor_id' => 6, 'pensum_course_id' => 12, 'unit' => 1, 'created_at' => '2026-03-12 02:53:19', 'updated_at' => '2026-03-12 02:53:19'],
            ['id' => 66, 'classroom_id' => 2, 'professor_id' => 6, 'pensum_course_id' => 12, 'unit' => 2, 'created_at' => '2026-03-12 02:53:19', 'updated_at' => '2026-03-12 02:53:19'],
            ['id' => 67, 'classroom_id' => 2, 'professor_id' => 6, 'pensum_course_id' => 12, 'unit' => 3, 'created_at' => '2026-03-12 02:53:19', 'updated_at' => '2026-03-12 02:53:19'],
            ['id' => 68, 'classroom_id' => 2, 'professor_id' => 6, 'pensum_course_id' => 12, 'unit' => 4, 'created_at' => '2026-03-12 02:53:19', 'updated_at' => '2026-03-12 02:53:19'],
            ['id' => 69, 'classroom_id' => 2, 'professor_id' => 10, 'pensum_course_id' => 13, 'unit' => 1, 'created_at' => '2026-03-12 02:53:19', 'updated_at' => '2026-03-12 02:53:19'],
            ['id' => 70, 'classroom_id' => 2, 'professor_id' => 10, 'pensum_course_id' => 13, 'unit' => 2, 'created_at' => '2026-03-12 02:53:19', 'updated_at' => '2026-03-12 02:53:19'],
            ['id' => 71, 'classroom_id' => 2, 'professor_id' => 10, 'pensum_course_id' => 13, 'unit' => 3, 'created_at' => '2026-03-12 02:53:19', 'updated_at' => '2026-03-12 02:53:19'],
            ['id' => 72, 'classroom_id' => 2, 'professor_id' => 10, 'pensum_course_id' => 13, 'unit' => 4, 'created_at' => '2026-03-12 02:53:19', 'updated_at' => '2026-03-12 02:53:19'],
            ['id' => 73, 'classroom_id' => 2, 'professor_id' => 7, 'pensum_course_id' => 14, 'unit' => 1, 'created_at' => '2026-03-12 02:53:19', 'updated_at' => '2026-03-12 02:53:19'],
            ['id' => 74, 'classroom_id' => 2, 'professor_id' => 7, 'pensum_course_id' => 14, 'unit' => 2, 'created_at' => '2026-03-12 02:53:19', 'updated_at' => '2026-03-12 02:53:19'],
            ['id' => 75, 'classroom_id' => 2, 'professor_id' => 7, 'pensum_course_id' => 14, 'unit' => 3, 'created_at' => '2026-03-12 02:53:19', 'updated_at' => '2026-03-12 02:53:19'],
            ['id' => 76, 'classroom_id' => 2, 'professor_id' => 7, 'pensum_course_id' => 14, 'unit' => 4, 'created_at' => '2026-03-12 02:53:19', 'updated_at' => '2026-03-12 02:53:19'],
        ];

        foreach ($data as $item) {
            ClassroomCourseAssignment::updateOrCreate(['id' => $item['id']], $item);
        }
    }
}
