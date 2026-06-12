<?php

namespace Database\Seeders;

use App\Models\AdmissionCourse;
use Illuminate\Database\Seeder;

class AdmissionCoursesSeeder extends Seeder
{
    public function run(): void
    {
        $courses = [
            ['name' => 'Lenguaje',           'ordering' => 1],
            ['name' => 'Destrezas',          'ordering' => 2],
            ['name' => 'Matemáticas',        'ordering' => 3],
            ['name' => 'Inglés',             'ordering' => 4],
            ['name' => 'Educación Católica', 'ordering' => 5],
            ['name' => 'Tecnología',         'ordering' => 6],
        ];

        foreach ($courses as $course) {
            AdmissionCourse::firstOrCreate(
                ['name' => $course['name']],
                ['ordering' => $course['ordering']]
            );
        }
    }
}
