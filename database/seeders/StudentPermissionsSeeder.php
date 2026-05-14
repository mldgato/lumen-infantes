<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class StudentPermissionsSeeder extends Seeder
{
    /**
     * Agrega/renombra permisos del módulo Student. Seguro de re-ejecutar.
     * Si existen los nombres viejos (estudiante.*), los renombra in-place.
     */
    public function run(): void
    {
        $role3 = Role::findByName('Estudiante');

        $renames = [
            'estudiante.grades.view'      => 'student.grades.view',
            'estudiante.attendance.view'  => 'student.attendance.view',
            'estudiante.report-card.view' => 'student.report-card.view',
        ];

        foreach ($renames as $old => $new) {
            $perm = Permission::where('name', $old)->first();
            if ($perm) {
                $perm->name = $new;
                $perm->save();
            }
        }

        $permissions = [
            'student.grades.view'            => 'Estudiante - Ver mis calificaciones',
            'student.attendance.view'         => 'Estudiante - Ver mi asistencia',
            'student.report-card.view'        => 'Estudiante - Ver e imprimir mi boleta',
            'dashboard.panel.student-summary' => 'Dashboard - Panel resumen del estudiante',
        ];

        foreach ($permissions as $name => $description) {
            Permission::firstOrCreate(['name' => $name], ['description' => $description])
                ->syncRoles([$role3]);
        }
    }
}
