<?php

namespace Database\Seeders;

use App\Models\Classroom;
use Illuminate\Database\Seeder;

class ClassroomSeeder extends Seeder
{
    public function run(): void
    {
        $classrooms = [
            ['level_id' => 1, 'grade_id' => 1, 'section_id' => 1, 'year' => '2026'],
            ['level_id' => 1, 'grade_id' => 1, 'section_id' => 2, 'year' => '2026'],
            ['level_id' => 1, 'grade_id' => 2, 'section_id' => 1, 'year' => '2026'],
            ['level_id' => 1, 'grade_id' => 3, 'section_id' => 1, 'year' => '2026'],
            ['level_id' => 1, 'grade_id' => 3, 'section_id' => 2, 'year' => '2026'],
            ['level_id' => 2, 'grade_id' => 4, 'section_id' => 1, 'year' => '2026'],
            ['level_id' => 2, 'grade_id' => 5, 'section_id' => 1, 'year' => '2026'],
            ['level_id' => 2, 'grade_id' => 6, 'section_id' => 1, 'year' => '2026'],
            ['level_id' => 2, 'grade_id' => 7, 'section_id' => 1, 'year' => '2026'],
            ['level_id' => 2, 'grade_id' => 8, 'section_id' => 1, 'year' => '2026'],
        ];

        foreach ($classrooms as $classroom) {
            Classroom::create($classroom);
        }
    }
}
