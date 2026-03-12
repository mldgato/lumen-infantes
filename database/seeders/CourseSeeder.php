<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = [
            ['id' => 1, 'course_name' => 'Comunicación y Lenguaje Idioma Español', 'created_at' => '2026-03-12 14:16:28', 'updated_at' => '2026-03-12 14:16:28'],
            ['id' => 2, 'course_name' => 'Comunicación y Lenguaje Idioma Extranjero', 'created_at' => '2026-03-12 14:16:28', 'updated_at' => '2026-03-12 14:16:28'],
            ['id' => 3, 'course_name' => 'Matemáticas', 'created_at' => '2026-03-12 14:16:28', 'updated_at' => '2026-03-12 19:46:31'],
            ['id' => 4, 'course_name' => 'Ciencias Naturales', 'created_at' => '2026-03-12 14:16:28', 'updated_at' => '2026-03-12 14:16:28'],
            ['id' => 5, 'course_name' => 'Ciencias Sociales, Formación Ciudadana e Interculturalidad', 'created_at' => '2026-03-12 14:16:28', 'updated_at' => '2026-03-12 14:16:28'],
            ['id' => 6, 'course_name' => 'Culturas e Idiomas Maya, Garífuna o Xinca', 'created_at' => '2026-03-12 14:16:28', 'updated_at' => '2026-03-12 14:16:28'],
            ['id' => 7, 'course_name' => 'Educación Artística', 'created_at' => '2026-03-12 14:16:28', 'updated_at' => '2026-03-12 14:16:28'],
            ['id' => 8, 'course_name' => 'Educación Física', 'created_at' => '2026-03-12 14:16:28', 'updated_at' => '2026-03-12 14:16:28'],
            ['id' => 9, 'course_name' => 'Emprendimiento para la Productividad', 'created_at' => '2026-03-12 14:16:28', 'updated_at' => '2026-03-12 14:16:28'],
            ['id' => 10, 'course_name' => 'Tecnologías del Aprendizaje y la Comunicación', 'created_at' => '2026-03-12 14:16:28', 'updated_at' => '2026-03-12 14:16:28'],
            ['id' => 11, 'course_name' => 'Artes Plásticas', 'created_at' => '2026-03-12 14:16:28', 'updated_at' => '2026-03-12 14:16:28'],
            ['id' => 12, 'course_name' => 'Formación Musical', 'created_at' => '2026-03-12 14:16:28', 'updated_at' => '2026-03-12 14:16:28'],
            ['id' => 13, 'course_name' => 'Danza', 'created_at' => '2026-03-12 14:16:28', 'updated_at' => '2026-03-12 14:16:28'],
            ['id' => 14, 'course_name' => 'Teatro', 'created_at' => '2026-03-12 14:16:28', 'updated_at' => '2026-03-12 14:16:28'],
            ['id' => 15, 'course_name' => 'Lengua y Literatura', 'created_at' => '2026-03-12 19:45:31', 'updated_at' => '2026-03-12 19:45:31'],
            ['id' => 16, 'course_name' => 'Comunicación y Lenguaje L3', 'created_at' => '2026-03-12 19:45:45', 'updated_at' => '2026-03-12 19:45:45'],
            ['id' => 17, 'course_name' => 'TIC I', 'created_at' => '2026-03-12 19:46:01', 'updated_at' => '2026-03-12 19:46:01'],
            ['id' => 18, 'course_name' => 'TIC II', 'created_at' => '2026-03-12 19:46:06', 'updated_at' => '2026-03-12 19:46:06'],
            ['id' => 19, 'course_name' => 'Estadística Descriptiva', 'created_at' => '2026-03-12 19:46:48', 'updated_at' => '2026-03-12 19:46:48'],
            ['id' => 20, 'course_name' => 'Ciencias Sociales y Formación Ciudadana I', 'created_at' => '2026-03-12 19:47:03', 'updated_at' => '2026-03-12 19:47:03'],
            ['id' => 21, 'course_name' => 'Ciencias Sociales y Formación Ciudadana II', 'created_at' => '2026-03-12 19:47:08', 'updated_at' => '2026-03-12 19:47:08'],
            ['id' => 22, 'course_name' => 'Física', 'created_at' => '2026-03-12 19:47:25', 'updated_at' => '2026-03-12 19:47:25'],
            ['id' => 23, 'course_name' => 'Química', 'created_at' => '2026-03-12 19:47:32', 'updated_at' => '2026-03-12 19:47:32'],
            ['id' => 24, 'course_name' => 'Biología', 'created_at' => '2026-03-12 19:47:40', 'updated_at' => '2026-03-12 19:47:40'],
            ['id' => 25, 'course_name' => 'Expresión Artística', 'created_at' => '2026-03-12 19:48:05', 'updated_at' => '2026-03-12 19:48:05'],
            ['id' => 26, 'course_name' => 'Filosofía', 'created_at' => '2026-03-12 19:48:15', 'updated_at' => '2026-03-12 19:48:15'],
            ['id' => 27, 'course_name' => 'Psicología', 'created_at' => '2026-03-12 19:48:22', 'updated_at' => '2026-03-12 19:48:22'],
            ['id' => 28, 'course_name' => 'Elaboración y Gestión de Proyectos', 'created_at' => '2026-03-12 19:48:40', 'updated_at' => '2026-03-12 19:48:40'],
            ['id' => 29, 'course_name' => 'Seminario', 'created_at' => '2026-03-12 19:48:47', 'updated_at' => '2026-03-12 19:48:47'],
            ['id' => 30, 'course_name' => 'Contabilidad de Sociedades', 'created_at' => '2026-03-12 19:56:49', 'updated_at' => '2026-03-12 19:56:49'],
            ['id' => 31, 'course_name' => 'Matemática Comercial', 'created_at' => '2026-03-12 19:56:55', 'updated_at' => '2026-03-12 19:56:55'],
            ['id' => 32, 'course_name' => 'Fundamentos de Derecho', 'created_at' => '2026-03-12 19:57:01', 'updated_at' => '2026-03-12 19:57:01'],
            ['id' => 33, 'course_name' => 'Inglés Comercial I', 'created_at' => '2026-03-12 19:57:06', 'updated_at' => '2026-03-12 19:57:06'],
            ['id' => 34, 'course_name' => 'Redacción y Correspondencia Mercantil', 'created_at' => '2026-03-12 19:57:11', 'updated_at' => '2026-03-12 19:57:11'],
            ['id' => 35, 'course_name' => 'Introducción a la Economía', 'created_at' => '2026-03-12 19:57:18', 'updated_at' => '2026-03-12 19:57:18'],
            ['id' => 36, 'course_name' => 'Ortografía y Caligrafía', 'created_at' => '2026-03-12 19:57:23', 'updated_at' => '2026-03-12 19:57:23'],
            ['id' => 37, 'course_name' => 'Administración y Organización de Oficina', 'created_at' => '2026-03-12 19:57:29', 'updated_at' => '2026-03-12 19:57:29'],
            ['id' => 38, 'course_name' => 'Computación I', 'created_at' => '2026-03-12 19:57:35', 'updated_at' => '2026-03-12 19:57:35'],
            ['id' => 39, 'course_name' => 'Contabilidad de Costos', 'created_at' => '2026-03-12 19:57:42', 'updated_at' => '2026-03-12 19:57:42'],
            ['id' => 40, 'course_name' => 'Cálculo Marcantil y Financiero', 'created_at' => '2026-03-12 19:57:49', 'updated_at' => '2026-03-12 19:57:49'],
            ['id' => 41, 'course_name' => 'Inglés Comercial II', 'created_at' => '2026-03-12 19:57:55', 'updated_at' => '2026-03-12 19:57:55'],
            ['id' => 42, 'course_name' => 'Legislación Fiscal y Aduanal', 'created_at' => '2026-03-12 19:58:01', 'updated_at' => '2026-03-12 19:58:01'],
            ['id' => 43, 'course_name' => 'Finanzas Públicas', 'created_at' => '2026-03-12 19:58:07', 'updated_at' => '2026-03-12 19:58:07'],
            ['id' => 44, 'course_name' => 'Geografía Económica', 'created_at' => '2026-03-12 19:58:14', 'updated_at' => '2026-03-12 19:58:14'],
            ['id' => 45, 'course_name' => 'Catalogación y Archivo', 'created_at' => '2026-03-12 19:58:21', 'updated_at' => '2026-03-12 19:58:21'],
            ['id' => 46, 'course_name' => 'Mecanografía', 'created_at' => '2026-03-12 19:58:31', 'updated_at' => '2026-03-12 19:58:31'],
            ['id' => 47, 'course_name' => 'Computación II', 'created_at' => '2026-03-12 19:58:58', 'updated_at' => '2026-03-12 19:58:58'],
            ['id' => 48, 'course_name' => 'Contabilidad Bancaria', 'created_at' => '2026-03-12 19:59:31', 'updated_at' => '2026-03-12 19:59:31'],
            ['id' => 49, 'course_name' => 'Estadística Comercial', 'created_at' => '2026-03-12 19:59:37', 'updated_at' => '2026-03-12 19:59:37'],
            ['id' => 50, 'course_name' => 'Contabilidad Gubernamental Integrada', 'created_at' => '2026-03-12 19:59:43', 'updated_at' => '2026-03-12 19:59:43'],
            ['id' => 51, 'course_name' => 'Organización de Empresas', 'created_at' => '2026-03-12 19:59:48', 'updated_at' => '2026-03-12 19:59:48'],
            ['id' => 52, 'course_name' => 'Ética Profesional y Relaciones Humanas', 'created_at' => '2026-03-12 19:59:55', 'updated_at' => '2026-03-12 19:59:55'],
            ['id' => 53, 'course_name' => 'Práctica Supervisada', 'created_at' => '2026-03-12 20:00:01', 'updated_at' => '2026-03-12 20:00:01'],
            ['id' => 54, 'course_name' => 'Auditoría', 'created_at' => '2026-03-12 20:00:07', 'updated_at' => '2026-03-12 20:00:07'],
            ['id' => 55, 'course_name' => 'Derecho Mercantil y Nociones de Derecho Laboral', 'created_at' => '2026-03-12 20:00:14', 'updated_at' => '2026-03-12 20:00:14'],
            ['id' => 56, 'course_name' => 'Computación III', 'created_at' => '2026-03-12 20:00:26', 'updated_at' => '2026-03-12 20:00:26'],
            ['id' => 57, 'course_name' => 'Seminario sobre Problemas Socio Económicos de Guatemala', 'created_at' => '2026-03-12 20:00:32', 'updated_at' => '2026-03-12 20:00:32'],
            ['id' => 58, 'course_name' => 'Programación I', 'created_at' => '2026-03-12 20:00:32', 'updated_at' => '2026-03-12 20:00:32'],
            ['id' => 59, 'course_name' => 'Programación II', 'created_at' => '2026-03-12 20:00:32', 'updated_at' => '2026-03-12 20:00:32'],
            ['id' => 60, 'course_name' => 'Programación III', 'created_at' => '2026-03-12 20:00:32', 'updated_at' => '2026-03-12 20:00:32'],
        ];

        foreach ($courses as $course) {
            Course::updateOrCreate(['id' => $course['id']], $course);
        }
    }
}
