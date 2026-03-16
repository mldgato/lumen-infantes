<?php

namespace Database\Seeders;

use App\Models\PensumCourse;
use Illuminate\Database\Seeder;

class PensumCourseSeeder extends Seeder
{
    public function run(): void
    {
        $pensums = [
            // Pensums 1, 2 y 3
            1 => [1, 2, 3, 4, 5, 6, 11, 12, 8, 9, 10],
            2 => [1, 2, 3, 4, 5, 6, 11, 12, 8, 9, 10],
            3 => [1, 2, 3, 4, 5, 6, 11, 12, 8, 9, 10],

            // Pensum 4
            4 => [15, 16, 17, 3, 20, 22, 8, 26, 27, 28],

            // Pensum 5
            5 => [15, 16, 18, 3, 19, 21, 23, 24, 25, 29],

            // Pensum 6
            6 => [30, 31, 32, 33, 34, 35, 36, 37, 38],

            // Pensum 7
            7 => [39, 40, 41, 42, 43, 44, 45, 46, 47],

            // Pensum 8
            8 => [48, 49, 50, 51, 52, 53, 54, 55, 56, 57],
        ];

        foreach ($pensums as $pensumId => $courses) {
            $units = ($pensumId === 8) ? [1, 2, 3] : [1, 2, 3, 4];

            foreach ($courses as $index => $courseId) {
                PensumCourse::updateOrCreate(
                    [
                        'pensum_id' => $pensumId,
                        'course_id' => $courseId,
                    ],
                    [
                        'parent_id'   => null,
                        'units'       => $units,
                        'is_main'     => 0,
                        'is_official' => 1,
                        'ordering'    => $index + 1,
                    ]
                );
            }
        }
    }
}
