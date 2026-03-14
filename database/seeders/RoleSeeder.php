<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role1 = Role::create(['name' => 'Super Administrador']);
        $role2 = Role::create(['name' => 'Administrador']);
        $role3 = Role::create(['name' => 'Estudiante']);
        $role4 = Role::create(['name' => 'Profesor']);

        Permission::create([
            'name' => 'admin.menu',
            'description' => 'Administración - Menú Administrador'
        ])->assignRole($role1);

        Permission::create([
            'name' => 'admin.users.index',
            'description' => 'Administración - Listado de usuarios'
        ])->assignRole($role1);

        Permission::create([
            'name' => 'admin.students.index',
            'description' => 'Administración - Listado de estudiantes'
        ])->assignRole($role1);

        // Niveles
        Permission::create([
            'name'        => 'admin.levels.index',
            'description' => 'Administración - Listado de niveles'
        ])->assignRole($role1);

        Permission::create([
            'name'        => 'admin.levels.create',
            'description' => 'Administración - Crear niveles'
        ])->assignRole($role1);

        Permission::create([
            'name'        => 'admin.levels.edit',
            'description' => 'Administración - Editar niveles'
        ])->assignRole($role1);

        Permission::create([
            'name'        => 'admin.levels.delete',
            'description' => 'Administración - Eliminar niveles'
        ])->assignRole($role1);

        // Grados
        Permission::create([
            'name'        => 'admin.grades.index',
            'description' => 'Administración - Listado de grados'
        ])->assignRole($role1);

        Permission::create([
            'name'        => 'admin.grades.create',
            'description' => 'Administración - Crear grados'
        ])->assignRole($role1);

        Permission::create([
            'name'        => 'admin.grades.edit',
            'description' => 'Administración - Editar grados'
        ])->assignRole($role1);

        Permission::create([
            'name'        => 'admin.grades.delete',
            'description' => 'Administración - Eliminar grados'
        ])->assignRole($role1);

        // Secciones
        Permission::create([
            'name'        => 'admin.sections.index',
            'description' => 'Administración - Listado de secciones'
        ])->assignRole($role1);

        Permission::create([
            'name'        => 'admin.sections.create',
            'description' => 'Administración - Crear secciones'
        ])->assignRole($role1);

        Permission::create([
            'name'        => 'admin.sections.edit',
            'description' => 'Administración - Editar secciones'
        ])->assignRole($role1);

        Permission::create([
            'name'        => 'admin.sections.delete',
            'description' => 'Administración - Eliminar secciones'
        ])->assignRole($role1);

        // Aulas
        Permission::create([
            'name'        => 'admin.classrooms.index',
            'description' => 'Administración - Listado de aulas'
        ])->assignRole($role1);

        Permission::create([
            'name'        => 'admin.classrooms.create',
            'description' => 'Administración - Crear aulas'
        ])->assignRole($role1);

        Permission::create([
            'name'        => 'admin.classrooms.edit',
            'description' => 'Administración - Editar aulas'
        ])->assignRole($role1);

        Permission::create([
            'name'        => 'admin.classrooms.delete',
            'description' => 'Administración - Eliminar aulas'
        ])->assignRole($role1);

        // Cursos
        Permission::create([
            'name'        => 'admin.courses.index',
            'description' => 'Administración - Listado de cursos'
        ])->assignRole($role1);

        Permission::create([
            'name'        => 'admin.courses.create',
            'description' => 'Administración - Crear cursos'
        ])->assignRole($role1);

        Permission::create([
            'name'        => 'admin.courses.edit',
            'description' => 'Administración - Editar cursos'
        ])->assignRole($role1);

        Permission::create([
            'name'        => 'admin.courses.delete',
            'description' => 'Administración - Eliminar cursos'
        ])->assignRole($role1);

        // Pénsum
        Permission::create([
            'name'        => 'admin.pensums.index',
            'description' => 'Administración - Listado de pénsum'
        ])->assignRole($role1);

        Permission::create([
            'name'        => 'admin.pensums.create',
            'description' => 'Administración - Crear pénsum'
        ])->assignRole($role1);

        Permission::create([
            'name'        => 'admin.pensums.edit',
            'description' => 'Administración - Editar pénsum'
        ])->assignRole($role1);

        Permission::create([
            'name'        => 'admin.pensums.delete',
            'description' => 'Administración - Eliminar pénsum'
        ])->assignRole($role1);

        // Asignación de Profesores
        Permission::create([
            'name'        => 'admin.classroom-course-assignments.index',
            'description' => 'Administración - Listado de asignación de profesores'
        ])->assignRole($role1);

        Permission::create([
            'name'        => 'admin.classroom-course-assignments.create',
            'description' => 'Administración - Crear asignación de profesores'
        ])->assignRole($role1);

        Permission::create([
            'name'        => 'admin.classroom-course-assignments.edit',
            'description' => 'Administración - Editar asignación de profesores'
        ])->assignRole($role1);

        Permission::create([
            'name'        => 'admin.classroom-course-assignments.delete',
            'description' => 'Administración - Eliminar asignación de profesores'
        ])->assignRole($role1);

        // Configuración Académica
        Permission::create([
            'name'        => 'admin.academic-configurations.index',
            'description' => 'Administración - Listado de configuración académica'
        ])->assignRole($role1);

        Permission::create([
            'name'        => 'admin.academic-configurations.create',
            'description' => 'Administración - Crear configuración académica'
        ])->assignRole($role1);

        Permission::create([
            'name'        => 'admin.academic-configurations.edit',
            'description' => 'Administración - Editar configuración académica'
        ])->assignRole($role1);

        Permission::create([
            'name'        => 'admin.academic-configurations.delete',
            'description' => 'Administración - Eliminar configuración académica'
        ])->assignRole($role1);

        // Cuadros de Calificaciones - Admin
        Permission::create([
            'name'        => 'admin.grade-books.index',
            'description' => 'Administración - Listado de cuadros de calificaciones'
        ])->assignRole($role1);

        Permission::create([
            'name'        => 'admin.grade-books.approve',
            'description' => 'Administración - Aprobar cuadros de calificaciones'
        ])->assignRole($role1);

        Permission::create([
            'name'        => 'admin.grade-books.reject',
            'description' => 'Administración - Rechazar cuadros de calificaciones'
        ])->assignRole($role1);

        // Cuadros de Calificaciones - Profesor
        Permission::create([
            'name'        => 'profesor.grade-books.index',
            'description' => 'Profesor - Listado de cuadros de calificaciones'
        ])->assignRole($role4);

        Permission::create([
            'name'        => 'profesor.grade-books.create',
            'description' => 'Profesor - Crear cuadros de calificaciones'
        ])->assignRole($role4);

        Permission::create([
            'name'        => 'profesor.grade-books.edit',
            'description' => 'Profesor - Editar cuadros de calificaciones'
        ])->assignRole($role4);

        Permission::create([
            'name'        => 'profesor.grade-books.lock',
            'description' => 'Profesor - Bloquear cuadros de calificaciones'
        ])->assignRole($role4);

        Permission::create([
            'name'        => 'admin.reports.sabana-unidad',
            'description' => 'Administración - Generar sábana por unidad'
        ])->assignRole($role1);

        Permission::create(['name' => 'admin.roles.index',       'description' => 'Ver roles'])->assignRole($role1);
        Permission::create(['name' => 'admin.permissions.index', 'description' => 'Ver permisos'])->assignRole($role1);
        Permission::create(['name' => 'admin.reports.sabana-unidad', 'description' => 'Reporte sábana por unidad'])->assignRole($role1);
    }
}
