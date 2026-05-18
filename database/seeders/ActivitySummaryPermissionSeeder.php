<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ActivitySummaryPermissionSeeder extends Seeder
{
    /**
     * Agrega el permiso del reporte Resumen de Actividades. Seguro de re-ejecutar.
     */
    public function run(): void
    {
        $role1 = Role::findByName('Super Administrador');
        $role2 = Role::findByName('Director');

        Permission::firstOrCreate(
            ['name' => 'admin.reports.activity-summary'],
            ['description' => 'Resumen consolidado de actividades por estudiante']
        )->syncRoles([$role1, $role2]);
    }
}
