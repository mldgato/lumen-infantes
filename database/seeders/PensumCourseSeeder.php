<?php

namespace Database\Seeders;

use App\Models\PensumCourse;
use Illuminate\Database\Seeder;

class PensumCourseSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            // Pensum ID: 1
            ['id' => 1, 'pensum_id' => 1, 'course_id' => 1, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 1, 'created_at' => '2026-03-12 20:11:13', 'updated_at' => '2026-03-12 20:11:13'],
            ['id' => 2, 'pensum_id' => 1, 'course_id' => 2, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 2, 'created_at' => '2026-03-12 20:11:27', 'updated_at' => '2026-03-12 20:11:27'],
            ['id' => 3, 'pensum_id' => 1, 'course_id' => 3, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 3, 'created_at' => '2026-03-12 20:11:42', 'updated_at' => '2026-03-12 20:11:42'],
            ['id' => 4, 'pensum_id' => 1, 'course_id' => 4, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 4, 'created_at' => '2026-03-12 20:11:56', 'updated_at' => '2026-03-12 20:11:56'],
            ['id' => 5, 'pensum_id' => 1, 'course_id' => 5, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 5, 'created_at' => '2026-03-12 20:12:08', 'updated_at' => '2026-03-12 20:12:08'],
            ['id' => 6, 'pensum_id' => 1, 'course_id' => 7, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 7, 'created_at' => '2026-03-12 20:12:44', 'updated_at' => '2026-03-12 20:12:44'],
            ['id' => 7, 'pensum_id' => 1, 'course_id' => 8, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 8, 'created_at' => '2026-03-12 20:12:58', 'updated_at' => '2026-03-12 20:12:58'],
            ['id' => 8, 'pensum_id' => 1, 'course_id' => 9, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 9, 'created_at' => '2026-03-12 20:13:11', 'updated_at' => '2026-03-12 20:13:11'],
            ['id' => 9, 'pensum_id' => 1, 'course_id' => 10, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 10, 'created_at' => '2026-03-12 20:13:28', 'updated_at' => '2026-03-12 20:13:28'],

            // Pensum ID: 2
            ['id' => 10, 'pensum_id' => 2, 'course_id' => 1, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 1, 'created_at' => '2026-03-12 20:27:12', 'updated_at' => '2026-03-12 20:27:12'],
            ['id' => 11, 'pensum_id' => 2, 'course_id' => 2, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 2, 'created_at' => '2026-03-12 20:27:12', 'updated_at' => '2026-03-12 20:27:12'],
            ['id' => 12, 'pensum_id' => 2, 'course_id' => 3, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 3, 'created_at' => '2026-03-12 20:27:12', 'updated_at' => '2026-03-12 20:27:12'],
            ['id' => 13, 'pensum_id' => 2, 'course_id' => 4, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 4, 'created_at' => '2026-03-12 20:27:12', 'updated_at' => '2026-03-12 20:27:12'],
            ['id' => 14, 'pensum_id' => 2, 'course_id' => 5, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 5, 'created_at' => '2026-03-12 20:27:12', 'updated_at' => '2026-03-12 20:27:12'],
            ['id' => 15, 'pensum_id' => 2, 'course_id' => 7, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 7, 'created_at' => '2026-03-12 20:27:12', 'updated_at' => '2026-03-12 20:27:12'],
            ['id' => 16, 'pensum_id' => 2, 'course_id' => 8, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 8, 'created_at' => '2026-03-12 20:27:12', 'updated_at' => '2026-03-12 20:27:12'],
            ['id' => 17, 'pensum_id' => 2, 'course_id' => 9, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 9, 'created_at' => '2026-03-12 20:27:12', 'updated_at' => '2026-03-12 20:27:12'],
            ['id' => 18, 'pensum_id' => 2, 'course_id' => 10, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 10, 'created_at' => '2026-03-12 20:27:12', 'updated_at' => '2026-03-12 20:27:12'],

            // Pensum ID: 3
            ['id' => 19, 'pensum_id' => 3, 'course_id' => 1, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 1, 'created_at' => '2026-03-12 20:27:19', 'updated_at' => '2026-03-12 20:27:19'],
            ['id' => 20, 'pensum_id' => 3, 'course_id' => 2, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 2, 'created_at' => '2026-03-12 20:27:19', 'updated_at' => '2026-03-12 20:27:19'],
            ['id' => 21, 'pensum_id' => 3, 'course_id' => 3, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 3, 'created_at' => '2026-03-12 20:27:19', 'updated_at' => '2026-03-12 20:27:19'],
            ['id' => 22, 'pensum_id' => 3, 'course_id' => 4, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 4, 'created_at' => '2026-03-12 20:27:19', 'updated_at' => '2026-03-12 20:27:19'],
            ['id' => 23, 'pensum_id' => 3, 'course_id' => 5, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 5, 'created_at' => '2026-03-12 20:27:19', 'updated_at' => '2026-03-12 20:27:19'],
            ['id' => 24, 'pensum_id' => 3, 'course_id' => 7, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 7, 'created_at' => '2026-03-12 20:27:19', 'updated_at' => '2026-03-12 20:27:19'],
            ['id' => 25, 'pensum_id' => 3, 'course_id' => 8, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 8, 'created_at' => '2026-03-12 20:27:19', 'updated_at' => '2026-03-12 20:27:19'],
            ['id' => 26, 'pensum_id' => 3, 'course_id' => 9, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 9, 'created_at' => '2026-03-12 20:27:19', 'updated_at' => '2026-03-12 20:27:19'],
            ['id' => 27, 'pensum_id' => 3, 'course_id' => 10, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 10, 'created_at' => '2026-03-12 20:27:19', 'updated_at' => '2026-03-12 20:27:19'],

            // Pensum ID: 4
            ['id' => 28, 'pensum_id' => 4, 'course_id' => 15, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 1, 'created_at' => '2026-03-12 20:33:05', 'updated_at' => '2026-03-12 20:33:05'],
            ['id' => 29, 'pensum_id' => 4, 'course_id' => 16, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 2, 'created_at' => '2026-03-12 20:33:20', 'updated_at' => '2026-03-12 20:33:20'],
            ['id' => 30, 'pensum_id' => 4, 'course_id' => 17, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 3, 'created_at' => '2026-03-12 20:34:08', 'updated_at' => '2026-03-12 20:34:08'],
            ['id' => 31, 'pensum_id' => 4, 'course_id' => 3, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 4, 'created_at' => '2026-03-12 20:34:20', 'updated_at' => '2026-03-12 20:34:20'],
            ['id' => 32, 'pensum_id' => 4, 'course_id' => 20, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 5, 'created_at' => '2026-03-12 20:34:36', 'updated_at' => '2026-03-12 20:34:36'],
            ['id' => 33, 'pensum_id' => 4, 'course_id' => 8, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 7, 'created_at' => '2026-03-12 20:34:46', 'updated_at' => '2026-03-12 20:35:30'],
            ['id' => 34, 'pensum_id' => 4, 'course_id' => 26, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 8, 'created_at' => '2026-03-12 20:35:05', 'updated_at' => '2026-03-12 20:35:05'],
            ['id' => 35, 'pensum_id' => 4, 'course_id' => 22, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 6, 'created_at' => '2026-03-12 20:35:43', 'updated_at' => '2026-03-12 20:35:43'],
            ['id' => 36, 'pensum_id' => 4, 'course_id' => 27, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 9, 'created_at' => '2026-03-12 20:35:53', 'updated_at' => '2026-03-12 20:35:53'],
            ['id' => 37, 'pensum_id' => 4, 'course_id' => 28, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 10, 'created_at' => '2026-03-12 20:36:13', 'updated_at' => '2026-03-12 20:36:13'],

            // Pensum ID: 5
            ['id' => 38, 'pensum_id' => 5, 'course_id' => 15, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 1, 'created_at' => '2026-03-12 20:41:57', 'updated_at' => '2026-03-12 20:41:57'],
            ['id' => 39, 'pensum_id' => 5, 'course_id' => 16, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 2, 'created_at' => '2026-03-12 20:42:10', 'updated_at' => '2026-03-12 20:42:10'],
            ['id' => 40, 'pensum_id' => 5, 'course_id' => 18, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 3, 'created_at' => '2026-03-12 20:42:18', 'updated_at' => '2026-03-12 20:42:18'],
            ['id' => 41, 'pensum_id' => 5, 'course_id' => 3, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 4, 'created_at' => '2026-03-12 20:42:33', 'updated_at' => '2026-03-12 20:42:33'],
            ['id' => 42, 'pensum_id' => 5, 'course_id' => 19, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 5, 'created_at' => '2026-03-12 20:42:55', 'updated_at' => '2026-03-12 20:42:55'],
            ['id' => 43, 'pensum_id' => 5, 'course_id' => 21, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 6, 'created_at' => '2026-03-12 20:43:09', 'updated_at' => '2026-03-12 20:43:09'],
            ['id' => 44, 'pensum_id' => 5, 'course_id' => 23, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 7, 'created_at' => '2026-03-12 20:43:26', 'updated_at' => '2026-03-12 20:43:26'],
            ['id' => 45, 'pensum_id' => 5, 'course_id' => 24, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 8, 'created_at' => '2026-03-12 20:43:39', 'updated_at' => '2026-03-12 20:43:39'],
            ['id' => 46, 'pensum_id' => 5, 'course_id' => 25, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 9, 'created_at' => '2026-03-12 20:43:57', 'updated_at' => '2026-03-12 20:43:57'],
            ['id' => 47, 'pensum_id' => 5, 'course_id' => 29, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 10, 'created_at' => '2026-03-12 20:44:20', 'updated_at' => '2026-03-12 20:44:20'],

            // Pensum ID: 6
            ['id' => 48, 'pensum_id' => 6, 'course_id' => 30, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 1, 'created_at' => '2026-03-12 20:44:56', 'updated_at' => '2026-03-12 20:44:56'],
            ['id' => 49, 'pensum_id' => 6, 'course_id' => 31, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 2, 'created_at' => '2026-03-12 20:45:09', 'updated_at' => '2026-03-12 20:45:09'],
            ['id' => 50, 'pensum_id' => 6, 'course_id' => 32, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 3, 'created_at' => '2026-03-12 20:45:20', 'updated_at' => '2026-03-12 20:45:20'],
            ['id' => 51, 'pensum_id' => 6, 'course_id' => 33, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 4, 'created_at' => '2026-03-12 20:45:33', 'updated_at' => '2026-03-12 20:45:33'],
            ['id' => 52, 'pensum_id' => 6, 'course_id' => 34, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 5, 'created_at' => '2026-03-12 20:45:47', 'updated_at' => '2026-03-12 20:45:47'],
            ['id' => 53, 'pensum_id' => 6, 'course_id' => 35, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 6, 'created_at' => '2026-03-12 20:46:07', 'updated_at' => '2026-03-12 20:46:07'],
            ['id' => 54, 'pensum_id' => 6, 'course_id' => 36, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 7, 'created_at' => '2026-03-12 20:46:19', 'updated_at' => '2026-03-12 20:46:19'],
            ['id' => 55, 'pensum_id' => 6, 'course_id' => 37, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 8, 'created_at' => '2026-03-12 20:46:33', 'updated_at' => '2026-03-12 20:46:33'],
            ['id' => 56, 'pensum_id' => 6, 'course_id' => 38, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 9, 'created_at' => '2026-03-12 20:46:50', 'updated_at' => '2026-03-12 20:46:50'],
            ['id' => 57, 'pensum_id' => 6, 'course_id' => 58, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 10, 'created_at' => '2026-03-12 20:46:58', 'updated_at' => '2026-03-12 20:46:58'],

            // Pensum ID: 7
            ['id' => 58, 'pensum_id' => 7, 'course_id' => 39, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 1, 'created_at' => '2026-03-12 20:47:52', 'updated_at' => '2026-03-12 20:47:52'],
            ['id' => 59, 'pensum_id' => 7, 'course_id' => 40, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 2, 'created_at' => '2026-03-12 20:48:03', 'updated_at' => '2026-03-12 20:48:03'],
            ['id' => 60, 'pensum_id' => 7, 'course_id' => 41, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 3, 'created_at' => '2026-03-12 20:48:12', 'updated_at' => '2026-03-12 20:48:12'],
            ['id' => 61, 'pensum_id' => 7, 'course_id' => 42, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 4, 'created_at' => '2026-03-12 20:48:24', 'updated_at' => '2026-03-12 20:48:24'],
            ['id' => 62, 'pensum_id' => 7, 'course_id' => 43, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 5, 'created_at' => '2026-03-12 20:48:35', 'updated_at' => '2026-03-12 20:48:35'],
            ['id' => 63, 'pensum_id' => 7, 'course_id' => 44, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 6, 'created_at' => '2026-03-12 20:48:47', 'updated_at' => '2026-03-12 20:48:47'],
            ['id' => 64, 'pensum_id' => 7, 'course_id' => 45, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 7, 'created_at' => '2026-03-12 20:49:04', 'updated_at' => '2026-03-12 20:49:04'],
            ['id' => 65, 'pensum_id' => 7, 'course_id' => 46, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 8, 'created_at' => '2026-03-12 20:49:18', 'updated_at' => '2026-03-12 20:49:18'],
            ['id' => 66, 'pensum_id' => 7, 'course_id' => 47, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 9, 'created_at' => '2026-03-12 20:49:27', 'updated_at' => '2026-03-12 20:49:27'],
            ['id' => 67, 'pensum_id' => 7, 'course_id' => 60, 'parent_id' => null, 'units' => [1, 2, 3, 4], 'is_main' => 0, 'is_official' => 1, 'ordering' => 10, 'created_at' => '2026-03-12 20:49:36', 'updated_at' => '2026-03-12 20:49:36'],

            // Pensum ID: 8
            ['id' => 68, 'pensum_id' => 8, 'course_id' => 48, 'parent_id' => null, 'units' => [1, 2, 3], 'is_main' => 0, 'is_official' => 1, 'ordering' => 1, 'created_at' => '2026-03-12 20:50:49', 'updated_at' => '2026-03-12 20:50:49'],
            ['id' => 69, 'pensum_id' => 8, 'course_id' => 49, 'parent_id' => null, 'units' => [1, 2, 3], 'is_main' => 0, 'is_official' => 1, 'ordering' => 2, 'created_at' => '2026-03-12 20:51:05', 'updated_at' => '2026-03-12 20:51:05'],
            ['id' => 70, 'pensum_id' => 8, 'course_id' => 50, 'parent_id' => null, 'units' => [1, 2, 3], 'is_main' => 0, 'is_official' => 1, 'ordering' => 3, 'created_at' => '2026-03-12 20:51:17', 'updated_at' => '2026-03-12 20:51:17'],
            ['id' => 71, 'pensum_id' => 8, 'course_id' => 51, 'parent_id' => null, 'units' => [1, 2, 3], 'is_main' => 0, 'is_official' => 1, 'ordering' => 4, 'created_at' => '2026-03-12 20:51:30', 'updated_at' => '2026-03-12 20:51:30'],
            ['id' => 72, 'pensum_id' => 8, 'course_id' => 52, 'parent_id' => null, 'units' => [1, 2, 3], 'is_main' => 0, 'is_official' => 1, 'ordering' => 5, 'created_at' => '2026-03-12 20:51:45', 'updated_at' => '2026-03-12 20:51:45'],
            ['id' => 73, 'pensum_id' => 8, 'course_id' => 53, 'parent_id' => null, 'units' => [1, 2, 3], 'is_main' => 0, 'is_official' => 1, 'ordering' => 6, 'created_at' => '2026-03-12 20:51:58', 'updated_at' => '2026-03-12 20:51:58'],
            ['id' => 74, 'pensum_id' => 8, 'course_id' => 54, 'parent_id' => null, 'units' => [1, 2, 3], 'is_main' => 0, 'is_official' => 1, 'ordering' => 7, 'created_at' => '2026-03-12 20:52:09', 'updated_at' => '2026-03-12 20:52:09'],
            ['id' => 75, 'pensum_id' => 8, 'course_id' => 55, 'parent_id' => null, 'units' => [1, 2, 3], 'is_main' => 0, 'is_official' => 1, 'ordering' => 8, 'created_at' => '2026-03-12 20:52:24', 'updated_at' => '2026-03-12 20:52:24'],
            ['id' => 76, 'pensum_id' => 8, 'course_id' => 56, 'parent_id' => null, 'units' => [1, 2, 3], 'is_main' => 0, 'is_official' => 1, 'ordering' => 9, 'created_at' => '2026-03-12 20:52:34', 'updated_at' => '2026-03-12 20:52:34'],
            ['id' => 77, 'pensum_id' => 8, 'course_id' => 57, 'parent_id' => null, 'units' => [1, 2, 3], 'is_main' => 0, 'is_official' => 1, 'ordering' => 10, 'created_at' => '2026-03-12 20:52:47', 'updated_at' => '2026-03-12 20:52:47'],
            ['id' => 78, 'pensum_id' => 8, 'course_id' => 60, 'parent_id' => null, 'units' => [1, 2, 3], 'is_main' => 0, 'is_official' => 1, 'ordering' => 11, 'created_at' => '2026-03-12 20:52:54', 'updated_at' => '2026-03-12 20:52:54'],
        ];

        foreach ($data as $item) {
            PensumCourse::updateOrCreate(['id' => $item['id']], $item);
        }
    }
}
