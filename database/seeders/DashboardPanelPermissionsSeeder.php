<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DashboardPanelPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = Role::findByName('Super Administrador');
        $director = Role::findByName('Director');
        $profesor = Role::findByName('Profesor');
        $secretaria = Role::findByName('Secretaria');

        $panels = [
            // Panel                                    Description                                      Roles
            ['dashboard.panel.stats-general',           'Dashboard - KPI: estudiantes, profesores, aulas',   [$superAdmin, $director, $secretaria]],
            ['dashboard.panel.grade-books-pending',     'Dashboard - KPI: cuadros pendientes de revisión',   [$superAdmin, $director]],
            ['dashboard.panel.students-by-grade',       'Dashboard - Gráfico: estudiantes por grado',        [$superAdmin, $director, $secretaria]],
            ['dashboard.panel.grade-books-status',      'Dashboard - Gráfico: estado de cuadros (dona)',     [$superAdmin, $director]],
            ['dashboard.panel.pending-change-requests', 'Dashboard - Tabla: solicitudes de cambio pendientes', [$superAdmin, $director]],
            ['dashboard.panel.locked-grade-books',      'Dashboard - Tabla: cuadros enviados a revisión',    [$superAdmin, $director]],
            ['dashboard.panel.profesor-stats',          'Dashboard - KPI: estadísticas del profesor',        [$superAdmin, $profesor]],
            ['dashboard.panel.profesor-grade-books-chart',   'Dashboard - Gráfico: cuadros por aula del profesor', [$superAdmin, $profesor]],
            ['dashboard.panel.profesor-grade-books-summary', 'Dashboard - Resumen: estado de cuadros del profesor', [$superAdmin, $profesor]],
            ['dashboard.panel.actionable-grade-books',  'Dashboard - Tabla: cuadros que requieren atención', [$superAdmin, $profesor]],
            ['dashboard.panel.birthday-students',       'Dashboard - Panel: cumpleañeros del mes (estudiantes)', [$superAdmin, $secretaria]],
            ['dashboard.panel.upcoming-birthdays',      'Dashboard - Panel: próximos cumpleaños del personal',  [$superAdmin, $secretaria]],
        ];

        foreach ($panels as [$name, $description, $roles]) {
            $permission = Permission::firstOrCreate(
                ['name' => $name],
                ['description' => $description]
            );
            $permission->syncRoles($roles);
        }
    }
}
