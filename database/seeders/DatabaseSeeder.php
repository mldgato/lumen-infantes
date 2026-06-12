<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Storage::disk('public')->deleteDirectory('userImages');
        Storage::disk('public')->makeDirectory('userImages');

        $this->call([
            RoleSeeder::class,
            LevelSeeder::class,
            GradeSeeder::class,
            SectionSeeder::class,
            ClassroomSeeder::class,
            StudentSeeder::class,
            UserSeeder::class,
            ProfessorSeeder::class,
            /* StudentEnrollmentSeeder::class, */
            CourseSeeder::class,
            /* PensumSeederVariant::class, */
            PensumSeeder::class,
            PensumCourseSeeder::class,
            /* ClassroomCourseAssignmentSeederVariant::class, */
            ClassroomCourseAssignmentSeeder::class,
            ActivityTypeSeeder::class,
            AcademicConfigurationSeeder::class,
            /* GradeBookSeederVariant::class, */
            GradeBookSeeder::class,
            SystemSettingsSeeder::class,
            AdmissionApplicationSeeder::class,
            AdmissionCoursesSeeder::class,
            AdmissionCoursesSeeder::class
        ]);
    }
}
