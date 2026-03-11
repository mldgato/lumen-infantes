<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $courses = [
            ['course_name' => 'Comunicación y Lenguaje Idioma Español',],
            ['course_name' => 'Comunicación y Lenguaje Idioma Extranjero',],
            ['course_name' => 'Matemática'],
            ['course_name' => 'Ciencias Naturales'],
            ['course_name' => 'Ciencias Sociales, Formación Ciudadana e Interculturalidad',],
            ['course_name' => 'Culturas e Idiomas Maya, Garífuna o Xinca',],
            ['course_name' => 'Educación Artística'],
            ['course_name' => 'Educación Física'],
            ['course_name' => 'Emprendimiento para la Productividad'],
            ['course_name' => 'Tecnologías del Aprendizaje y la Comunicación'],
            ['course_name' => 'Artes Plásticas'],
            ['course_name' => 'Formación Musical'],
            ['course_name' => 'Danza'],
            ['course_name' => 'Teatro'],
        ];

        foreach ($courses as $course) {
            Course::create($course);
        }
    }
}
