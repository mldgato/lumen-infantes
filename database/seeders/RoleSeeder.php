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
        $role2 = Role::create(['name' => 'Director']);
        $role3 = Role::create(['name' => 'Estudiante']);
        $role4 = Role::create(['name' => 'Profesor']);
        $role5 = Role::create(['name' => 'Secretaria']);

        Permission::create([
            'name' => 'admin.menu',
            'description' => 'Administración - Menú Administrador'
        ])->syncRoles([$role1, $role2]);

        Permission::create([
            'name' => 'admin.users.index',
            'description' => 'Administración - Listado de usuarios'
        ])->syncRoles([$role1, $role2]);

        Permission::create([
            'name' => 'admin.students.index',
            'description' => 'Administración - Listado de estudiantes'
        ])->syncRoles([$role1, $role2]);

        // Niveles
        Permission::create([
            'name'        => 'admin.levels.index',
            'description' => 'Administración - Listado de niveles'
        ])->syncRoles([$role1, $role2]);

        Permission::create([
            'name'        => 'admin.levels.create',
            'description' => 'Administración - Crear niveles'
        ])->syncRoles([$role1, $role2]);

        Permission::create([
            'name'        => 'admin.levels.edit',
            'description' => 'Administración - Editar niveles'
        ])->syncRoles([$role1, $role2]);

        Permission::create([
            'name'        => 'admin.levels.delete',
            'description' => 'Administración - Eliminar niveles'
        ])->syncRoles([$role1, $role2]);

        // Grados
        Permission::create([
            'name'        => 'admin.grades.index',
            'description' => 'Administración - Listado de grados'
        ])->syncRoles([$role1, $role2]);

        Permission::create([
            'name'        => 'admin.grades.create',
            'description' => 'Administración - Crear grados'
        ])->syncRoles([$role1, $role2]);

        Permission::create([
            'name'        => 'admin.grades.edit',
            'description' => 'Administración - Editar grados'
        ])->syncRoles([$role1, $role2]);

        Permission::create([
            'name'        => 'admin.grades.delete',
            'description' => 'Administración - Eliminar grados'
        ])->syncRoles([$role1, $role2]);

        // Secciones
        Permission::create([
            'name'        => 'admin.sections.index',
            'description' => 'Administración - Listado de secciones'
        ])->syncRoles([$role1, $role2]);

        Permission::create([
            'name'        => 'admin.sections.create',
            'description' => 'Administración - Crear secciones'
        ])->syncRoles([$role1, $role2]);

        Permission::create([
            'name'        => 'admin.sections.edit',
            'description' => 'Administración - Editar secciones'
        ])->syncRoles([$role1, $role2]);

        Permission::create([
            'name'        => 'admin.sections.delete',
            'description' => 'Administración - Eliminar secciones'
        ])->syncRoles([$role1, $role2]);

        // Aulas
        Permission::create([
            'name'        => 'admin.classrooms.index',
            'description' => 'Administración - Listado de aulas'
        ])->syncRoles([$role1, $role2]);

        Permission::create([
            'name'        => 'admin.classrooms.create',
            'description' => 'Administración - Crear aulas'
        ])->syncRoles([$role1, $role2]);

        Permission::create([
            'name'        => 'admin.classrooms.edit',
            'description' => 'Administración - Editar aulas'
        ])->syncRoles([$role1, $role2]);

        Permission::create([
            'name'        => 'admin.classrooms.delete',
            'description' => 'Administración - Eliminar aulas'
        ])->syncRoles([$role1, $role2]);

        // Cursos
        Permission::create([
            'name'        => 'admin.courses.index',
            'description' => 'Administración - Listado de cursos'
        ])->syncRoles([$role1, $role2]);

        Permission::create([
            'name'        => 'admin.courses.create',
            'description' => 'Administración - Crear cursos'
        ])->syncRoles([$role1, $role2]);

        Permission::create([
            'name'        => 'admin.courses.edit',
            'description' => 'Administración - Editar cursos'
        ])->syncRoles([$role1, $role2]);

        Permission::create([
            'name'        => 'admin.courses.delete',
            'description' => 'Administración - Eliminar cursos'
        ])->syncRoles([$role1, $role2]);

        // Pénsum
        Permission::create([
            'name'        => 'admin.pensums.index',
            'description' => 'Administración - Listado de pénsum'
        ])->syncRoles([$role1, $role2]);

        Permission::create([
            'name'        => 'admin.pensums.create',
            'description' => 'Administración - Crear pénsum'
        ])->syncRoles([$role1, $role2]);

        Permission::create([
            'name'        => 'admin.pensums.edit',
            'description' => 'Administración - Editar pénsum'
        ])->syncRoles([$role1, $role2]);

        Permission::create([
            'name'        => 'admin.pensums.delete',
            'description' => 'Administración - Eliminar pénsum'
        ])->syncRoles([$role1, $role2]);

        // Asignación de Profesores
        Permission::create([
            'name'        => 'admin.classroom-course-assignments.index',
            'description' => 'Administración - Listado de asignación de profesores'
        ])->syncRoles([$role1, $role2]);

        Permission::create([
            'name'        => 'admin.classroom-course-assignments.create',
            'description' => 'Administración - Crear asignación de profesores'
        ])->syncRoles([$role1, $role2]);

        Permission::create([
            'name'        => 'admin.classroom-course-assignments.edit',
            'description' => 'Administración - Editar asignación de profesores'
        ])->syncRoles([$role1, $role2]);

        Permission::create([
            'name'        => 'admin.classroom-course-assignments.delete',
            'description' => 'Administración - Eliminar asignación de profesores'
        ])->syncRoles([$role1, $role2]);

        // Configuración Académica
        Permission::create([
            'name'        => 'admin.academic-configurations.index',
            'description' => 'Administración - Listado de configuración académica'
        ])->syncRoles([$role1, $role2]);

        Permission::create([
            'name'        => 'admin.academic-configurations.create',
            'description' => 'Administración - Crear configuración académica'
        ])->syncRoles([$role1, $role2]);

        Permission::create([
            'name'        => 'admin.academic-configurations.edit',
            'description' => 'Administración - Editar configuración académica'
        ])->syncRoles([$role1, $role2]);

        Permission::create([
            'name'        => 'admin.academic-configurations.delete',
            'description' => 'Administración - Eliminar configuración académica'
        ])->syncRoles([$role1, $role2]);

        // Cuadros de Calificaciones - Admin
        Permission::create([
            'name'        => 'admin.grade-books.index',
            'description' => 'Administración - Listado de cuadros de calificaciones'
        ])->syncRoles([$role1, $role2]);

        Permission::create([
            'name'        => 'admin.grade-books.approve',
            'description' => 'Administración - Aprobar cuadros de calificaciones'
        ])->syncRoles([$role1, $role2]);

        Permission::create([
            'name'        => 'admin.grade-books.reject',
            'description' => 'Administración - Rechazar cuadros de calificaciones'
        ])->syncRoles([$role1, $role2]);

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
        ])->syncRoles([$role1, $role2]);

        Permission::create(['name' => 'admin.roles.index',       'description' => 'Ver roles'])->assignRole($role1);
        Permission::create(['name' => 'admin.permissions.index', 'description' => 'Ver permisos'])->assignRole($role1);
        Permission::create(['name' => 'admin.reports.sabana-general', 'description' => 'Reporte sábana general por unidades'])->syncRoles([$role1, $role2]);
        Permission::create(['name' => 'admin.reports.sabana-promedio', 'description' => 'Reporte sábana promedio final'])->syncRoles([$role1, $role2]);
        Permission::create(['name' => 'admin.reports.cuadros-classroom', 'description' => 'Descarga de cuadros por aula'])->syncRoles([$role1, $role2]);
        Permission::create(['name' => 'profesor.reports.sabana-promedio', 'description' => 'Sábana de calificaciones del profesor'])->assignRole($role4);
        Permission::create(['name' => 'profesor.reports.cuadro-vacio', 'description' => 'Imprimir cuadro vacío'])->assignRole($role4);
        Permission::create(['name' => 'profesor.reports.cuadros-unidad', 'description' => 'Mis cuadros por unidad'])->assignRole($role4);
        Permission::create(['name' => 'profesor.reports.student-list', 'description' => 'Listado de estudiantes PDF'])->assignRole($role4);
        Permission::create(['name' => 'profesor.reports.student-list-excel', 'description' => 'Listado de estudiantes Excel'])->assignRole($role4);
        Permission::create(['name' => 'admin.reports.student-list',       'description' => 'Listado de estudiantes PDF Administración'])->syncRoles([$role1, $role2]);
        Permission::create(['name' => 'admin.reports.student-list-excel', 'description' => 'Listado de estudiantes Excel Administración'])->syncRoles([$role1, $role2]);

        Permission::create(['name' => 'admin.grade-change-requests.index',   'description' => 'Ver solicitudes de cambio de notas'])->syncRoles([$role1, $role2]);
        Permission::create(['name' => 'admin.grade-change-requests.approve',  'description' => 'Aprobar o rechazar solicitudes de cambio de notas'])->syncRoles([$role1, $role2]);
        Permission::create(['name' => 'profesor.grade-change-requests.create', 'description' => 'Solicitar cambio de calificaciones'])->assignRole($role4);

        Permission::create(['name' => 'admin.reports.report-cards', 'description' => 'Boletas de calificaciones'])->syncRoles([$role1, $role2]);

        Permission::create(['name' => 'admin.reports.missing-activities',   'description' => 'Reporte actividades no entregadas (admin)'])->syncRoles([$role1, $role2]);
        Permission::create(['name' => 'profesor.reports.missing-activities', 'description' => 'Reporte actividades no entregadas (profesor)'])->assignRole($role4);

        Permission::create(['name' => 'admin.students.enrollments.index', 'description' => 'Gestión de inscripciones de estudiantes'])->syncRoles([$role1, $role2]);

        Permission::create(['name' => 'admin.audit.index', 'description' => 'Ver registro de auditoría'])->syncRoles([$role1, $role2]);

        // Permisos de menú admin
        Permission::create(['name' => 'admin.menu.personal',    'description' => 'Menú Gestión de Personal'])->syncRoles([$role1, $role2]);
        Permission::create(['name' => 'admin.menu.estudiantil', 'description' => 'Menú Gestión Estudiantil'])->syncRoles([$role1, $role2]);
        Permission::create(['name' => 'admin.menu.academica',   'description' => 'Menú Gestión Académica'])->syncRoles([$role1, $role2]);
        Permission::create(['name' => 'admin.menu.cuadros',     'description' => 'Menú Cuadros y Calificaciones'])->syncRoles([$role1, $role2]);
        Permission::create(['name' => 'admin.menu.reportes',    'description' => 'Menú Reportes Admin'])->syncRoles([$role1, $role2]);
        Permission::create(['name' => 'admin.menu.sistema',     'description' => 'Menú Sistema'])->syncRoles([$role1, $role2]);
        Permission::create(['name' => 'admin.menu.encabezado',     'description' => 'Encabezado de Administración'])->syncRoles([$role1, $role2]);

        // Permisos de menú profesor
        Permission::create(['name' => 'profesor.menu.cuadros',  'description' => 'Menú Mis Cuadros Profesor'])->assignRole($role4);
        Permission::create(['name' => 'profesor.menu.reportes', 'description' => 'Menú Reportes Profesor'])->assignRole($role4);

        Permission::create(['name' => 'admin.user.loginUser', 'description' => 'Adminsitración - Inpersonación'])->assignRole($role1);

        Permission::create(['name' => 'admin.secretary', 'description' => 'Dashboard de secretaria'])->assignRole($role5);

        Permission::create(['name' => 'admin.reports.professor-courses',     'description' => 'Administración - Reporte de cursos asignados a los profesores'])->syncRoles([$role1, $role2]);

        Permission::create(['name' => 'profesor.attendance.index',  'description' => 'Profesor - Tomar Asistencia'])->assignRole($role4);

        Permission::create(['name' => 'admin.reports.attendance',     'description' => 'Administración - Reporte de Asistencia'])->syncRoles([$role1, $role2]);

        Permission::create(['name' => 'admin.reports.grade-progress', 'description' => 'Administración - Reporte de avance de ingreso de calificaciones'])->syncRoles([$role1, $role2]);
    }
}
