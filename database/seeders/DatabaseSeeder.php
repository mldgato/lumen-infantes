<?php

namespace Database\Seeders;

use App\Models\User;
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
            UserSeeder::class,
            LevelSeeder::class,
            GradeSeeder::class,
            SectionSeeder::class,
            ClassroomSeeder::class,
            StudentSeeder::class,
            ProfessorSeeder::class,
            /* StudentEnrollmentSeeder::class, */
            CourseSeeder::class,
            PensumSeeder::class,
            PensumCourseSeeder::class,
            ClassroomCourseAssignmentSeeder::class,
            ActivityTypeSeeder::class,
            AcademicConfigurationSeeder::class,
            /* GradeBookSeeder::class, */
        ]);
    }
}
