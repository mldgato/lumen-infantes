<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PensumCourseSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            // Pensum ID: 1
            ['id' => 1, 'pensum_id' => 1, 'course_id' => 1, 'parent_id' => null, 'units' => '[1, 2, 3, 4]', 'is_main' => 0, 'ordering' => 1, 'created_at' => '2026-03-12 01:55:31', 'updated_at' => '2026-03-12 01:55:31'],
            ['id' => 2, 'pensum_id' => 1, 'course_id' => 2, 'parent_id' => null, 'units' => '[1, 2, 3, 4]', 'is_main' => 0, 'ordering' => 2, 'created_at' => '2026-03-12 01:55:43', 'updated_at' => '2026-03-12 01:56:23'],
            ['id' => 3, 'pensum_id' => 1, 'course_id' => 3, 'parent_id' => null, 'units' => '[1, 2, 3, 4]', 'is_main' => 0, 'ordering' => 3, 'created_at' => '2026-03-12 01:55:57', 'updated_at' => '2026-03-12 01:56:18'],
            ['id' => 4, 'pensum_id' => 1, 'course_id' => 4, 'parent_id' => null, 'units' => '[1, 2, 3, 4]', 'is_main' => 0, 'ordering' => 4, 'created_at' => '2026-03-12 01:56:13', 'updated_at' => '2026-03-12 01:56:13'],
            ['id' => 5, 'pensum_id' => 1, 'course_id' => 5, 'parent_id' => null, 'units' => '[1, 2, 3, 4]', 'is_main' => 0, 'ordering' => 5, 'created_at' => '2026-03-12 01:56:58', 'updated_at' => '2026-03-12 01:56:58'],
            ['id' => 6, 'pensum_id' => 1, 'course_id' => 6, 'parent_id' => null, 'units' => '[1, 2]', 'is_main' => 0, 'ordering' => 6, 'created_at' => '2026-03-12 01:57:22', 'updated_at' => '2026-03-12 01:57:22'],
            ['id' => 7, 'pensum_id' => 1, 'course_id' => 7, 'parent_id' => null, 'units' => null, 'is_main' => 1, 'ordering' => 7, 'created_at' => '2026-03-12 01:58:14', 'updated_at' => '2026-03-12 01:58:14'],
            ['id' => 8, 'pensum_id' => 1, 'course_id' => 11, 'parent_id' => 7, 'units' => '[1]', 'is_main' => 0, 'ordering' => 0, 'created_at' => '2026-03-12 01:58:14', 'updated_at' => '2026-03-12 01:58:14'],
            ['id' => 9, 'pensum_id' => 1, 'course_id' => 12, 'parent_id' => 7, 'units' => '[2]', 'is_main' => 0, 'ordering' => 0, 'created_at' => '2026-03-12 01:58:14', 'updated_at' => '2026-03-12 01:58:14'],
            ['id' => 10, 'pensum_id' => 1, 'course_id' => 14, 'parent_id' => 7, 'units' => '[3]', 'is_main' => 0, 'ordering' => 0, 'created_at' => '2026-03-12 01:58:14', 'updated_at' => '2026-03-12 01:58:14'],
            ['id' => 11, 'pensum_id' => 1, 'course_id' => 13, 'parent_id' => 7, 'units' => '[4]', 'is_main' => 0, 'ordering' => 0, 'created_at' => '2026-03-12 01:58:14', 'updated_at' => '2026-03-12 01:58:14'],
            ['id' => 12, 'pensum_id' => 1, 'course_id' => 8, 'parent_id' => null, 'units' => '[1, 2, 3, 4]', 'is_main' => 0, 'ordering' => 8, 'created_at' => '2026-03-12 01:58:30', 'updated_at' => '2026-03-12 01:58:30'],
            ['id' => 13, 'pensum_id' => 1, 'course_id' => 9, 'parent_id' => null, 'units' => '[1, 2, 3, 4]', 'is_main' => 0, 'ordering' => 9, 'created_at' => '2026-03-12 01:59:00', 'updated_at' => '2026-03-12 01:59:00'],
            ['id' => 14, 'pensum_id' => 1, 'course_id' => 10, 'parent_id' => null, 'units' => '[1, 2, 3, 4]', 'is_main' => 0, 'ordering' => 10, 'created_at' => '2026-03-12 01:59:15', 'updated_at' => '2026-03-12 01:59:15'],

            // Pensum ID: 2
            ['id' => 15, 'pensum_id' => 2, 'course_id' => 1, 'parent_id' => null, 'units' => '[1, 2, 3, 4]', 'is_main' => 0, 'ordering' => 1, 'created_at' => '2026-03-12 02:05:53', 'updated_at' => '2026-03-12 02:05:53'],
            ['id' => 16, 'pensum_id' => 2, 'course_id' => 2, 'parent_id' => null, 'units' => '[1, 2, 3, 4]', 'is_main' => 0, 'ordering' => 2, 'created_at' => '2026-03-12 02:05:53', 'updated_at' => '2026-03-12 02:05:53'],
            ['id' => 17, 'pensum_id' => 2, 'course_id' => 3, 'parent_id' => null, 'units' => '[1, 2, 3, 4]', 'is_main' => 0, 'ordering' => 3, 'created_at' => '2026-03-12 02:05:53', 'updated_at' => '2026-03-12 02:05:53'],
            ['id' => 18, 'pensum_id' => 2, 'course_id' => 4, 'parent_id' => null, 'units' => '[1, 2, 3, 4]', 'is_main' => 0, 'ordering' => 4, 'created_at' => '2026-03-12 02:05:53', 'updated_at' => '2026-03-12 02:05:53'],
            ['id' => 19, 'pensum_id' => 2, 'course_id' => 5, 'parent_id' => null, 'units' => '[1, 2, 3, 4]', 'is_main' => 0, 'ordering' => 5, 'created_at' => '2026-03-12 02:05:53', 'updated_at' => '2026-03-12 02:05:53'],
            ['id' => 20, 'pensum_id' => 2, 'course_id' => 6, 'parent_id' => null, 'units' => '[1, 2]', 'is_main' => 0, 'ordering' => 6, 'created_at' => '2026-03-12 02:05:53', 'updated_at' => '2026-03-12 02:05:53'],
            ['id' => 21, 'pensum_id' => 2, 'course_id' => 7, 'parent_id' => null, 'units' => null, 'is_main' => 1, 'ordering' => 7, 'created_at' => '2026-03-12 02:05:53', 'updated_at' => '2026-03-12 02:05:53'],
            ['id' => 22, 'pensum_id' => 2, 'course_id' => 11, 'parent_id' => 21, 'units' => '[1]', 'is_main' => 0, 'ordering' => 0, 'created_at' => '2026-03-12 02:05:53', 'updated_at' => '2026-03-12 02:05:53'],
            ['id' => 23, 'pensum_id' => 2, 'course_id' => 12, 'parent_id' => 21, 'units' => '[2]', 'is_main' => 0, 'ordering' => 0, 'created_at' => '2026-03-12 02:05:53', 'updated_at' => '2026-03-12 02:05:53'],
            ['id' => 24, 'pensum_id' => 2, 'course_id' => 14, 'parent_id' => 21, 'units' => '[3]', 'is_main' => 0, 'ordering' => 0, 'created_at' => '2026-03-12 02:05:53', 'updated_at' => '2026-03-12 02:05:53'],
            ['id' => 25, 'pensum_id' => 2, 'course_id' => 13, 'parent_id' => 21, 'units' => '[4]', 'is_main' => 0, 'ordering' => 0, 'created_at' => '2026-03-12 02:05:53', 'updated_at' => '2026-03-12 02:05:53'],
            ['id' => 26, 'pensum_id' => 2, 'course_id' => 8, 'parent_id' => null, 'units' => '[1, 2, 3, 4]', 'is_main' => 0, 'ordering' => 8, 'created_at' => '2026-03-12 02:05:53', 'updated_at' => '2026-03-12 02:05:53'],
            ['id' => 27, 'pensum_id' => 2, 'course_id' => 9, 'parent_id' => null, 'units' => '[1, 2, 3, 4]', 'is_main' => 0, 'ordering' => 9, 'created_at' => '2026-03-12 02:05:53', 'updated_at' => '2026-03-12 02:05:53'],
            ['id' => 28, 'pensum_id' => 2, 'course_id' => 10, 'parent_id' => null, 'units' => '[1, 2, 3, 4]', 'is_main' => 0, 'ordering' => 10, 'created_at' => '2026-03-12 02:05:53', 'updated_at' => '2026-03-12 02:05:53'],

            // Pensum ID: 3
            ['id' => 29, 'pensum_id' => 3, 'course_id' => 1, 'parent_id' => null, 'units' => '[1, 2, 3, 4]', 'is_main' => 0, 'ordering' => 1, 'created_at' => '2026-03-12 02:06:53', 'updated_at' => '2026-03-12 02:06:53'],
            ['id' => 30, 'pensum_id' => 3, 'course_id' => 2, 'parent_id' => null, 'units' => '[1, 2, 3, 4]', 'is_main' => 0, 'ordering' => 2, 'created_at' => '2026-03-12 02:06:53', 'updated_at' => '2026-03-12 02:06:53'],
            ['id' => 31, 'pensum_id' => 3, 'course_id' => 3, 'parent_id' => null, 'units' => '[1, 2, 3, 4]', 'is_main' => 0, 'ordering' => 3, 'created_at' => '2026-03-12 02:06:53', 'updated_at' => '2026-03-12 02:06:53'],
            ['id' => 32, 'pensum_id' => 3, 'course_id' => 4, 'parent_id' => null, 'units' => '[1, 2, 3, 4]', 'is_main' => 0, 'ordering' => 4, 'created_at' => '2026-03-12 02:06:53', 'updated_at' => '2026-03-12 02:06:53'],
            ['id' => 33, 'pensum_id' => 3, 'course_id' => 5, 'parent_id' => null, 'units' => '[1, 2, 3, 4]', 'is_main' => 0, 'ordering' => 5, 'created_at' => '2026-03-12 02:06:53', 'updated_at' => '2026-03-12 02:06:53'],
            ['id' => 34, 'pensum_id' => 3, 'course_id' => 6, 'parent_id' => null, 'units' => '[1, 2]', 'is_main' => 0, 'ordering' => 6, 'created_at' => '2026-03-12 02:06:53', 'updated_at' => '2026-03-12 02:06:53'],
            ['id' => 35, 'pensum_id' => 3, 'course_id' => 7, 'parent_id' => null, 'units' => null, 'is_main' => 1, 'ordering' => 7, 'created_at' => '2026-03-12 02:06:53', 'updated_at' => '2026-03-12 02:06:53'],
            ['id' => 36, 'pensum_id' => 3, 'course_id' => 11, 'parent_id' => 35, 'units' => '[1]', 'is_main' => 0, 'ordering' => 0, 'created_at' => '2026-03-12 02:06:53', 'updated_at' => '2026-03-12 02:06:53'],
            ['id' => 37, 'pensum_id' => 3, 'course_id' => 12, 'parent_id' => 35, 'units' => '[2]', 'is_main' => 0, 'ordering' => 0, 'created_at' => '2026-03-12 02:06:53', 'updated_at' => '2026-03-12 02:06:53'],
            ['id' => 38, 'pensum_id' => 3, 'course_id' => 14, 'parent_id' => 35, 'units' => '[3]', 'is_main' => 0, 'ordering' => 0, 'created_at' => '2026-03-12 02:06:53', 'updated_at' => '2026-03-12 02:06:53'],
            ['id' => 39, 'pensum_id' => 3, 'course_id' => 13, 'parent_id' => 35, 'units' => '[4]', 'is_main' => 0, 'ordering' => 0, 'created_at' => '2026-03-12 02:06:53', 'updated_at' => '2026-03-12 02:06:53'],
            ['id' => 40, 'pensum_id' => 3, 'course_id' => 8, 'parent_id' => null, 'units' => '[1, 2, 3, 4]', 'is_main' => 0, 'ordering' => 8, 'created_at' => '2026-03-12 02:06:53', 'updated_at' => '2026-03-12 02:06:53'],
            ['id' => 41, 'pensum_id' => 3, 'course_id' => 9, 'parent_id' => null, 'units' => '[1, 2, 3, 4]', 'is_main' => 0, 'ordering' => 9, 'created_at' => '2026-03-12 02:06:53', 'updated_at' => '2026-03-12 02:06:53'],
            ['id' => 42, 'pensum_id' => 3, 'course_id' => 10, 'parent_id' => null, 'units' => '[1, 2, 3, 4]', 'is_main' => 0, 'ordering' => 10, 'created_at' => '2026-03-12 02:06:53', 'updated_at' => '2026-03-12 02:06:53'],
        ];

        DB::table('pensum_courses')->insert($data);
    }
}
