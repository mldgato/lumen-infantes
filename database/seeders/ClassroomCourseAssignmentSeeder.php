<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\ClassroomCourseAssignment;
use App\Models\Pensum;
use App\Models\Professor;
use Illuminate\Database\Seeder;

class ClassroomCourseAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $classrooms = Classroom::all();
        $professors = Professor::orderBy('id')->get();

        foreach ($classrooms as $classroom) {
            $pensum = Pensum::where('grade_id', $classroom->grade_id)
                ->where('year', $classroom->year)
                ->first();

            if (! $pensum) {
                continue;
            }

            $pensumCourses = $pensum->mainCourses()->orderBy('ordering')->get();

            foreach ($pensumCourses as $index => $pensumCourse) {
                $professor = $professors->get($index % $professors->count());

                $units = is_array($pensumCourse->units)
                    ? $pensumCourse->units
                    : json_decode($pensumCourse->units, true) ?? [1];

                foreach ($units as $unit) {
                    ClassroomCourseAssignment::updateOrCreate(
                        [
                            'classroom_id'     => $classroom->id,
                            'pensum_course_id' => $pensumCourse->id,
                            'unit'             => $unit,
                        ],
                        [
                            'professor_id' => $professor->id,
                        ]
                    );
                }
            }
        }
    }
}
