<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class StudentActivityDetailPermissionSeeder extends Seeder
{
    /**
     * Agrega el permiso del reporte Actividades por Estudiante. Seguro de re-ejecutar.
     */
    public function run(): void
    {
        $role1 = Role::findByName('Super Administrador');
        $role2 = Role::findByName('Director');

        Permission::firstOrCreate(
            ['name' => 'admin.reports.student-activity-detail'],
            ['description' => 'Informe detallado de actividades por estudiante']
        )->syncRoles([$role1, $role2]);
    }
}
