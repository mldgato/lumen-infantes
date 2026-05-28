<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ProfessorsGuardiansPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = Role::findByName('Super Administrador');
        $director = Role::findByName('Director');
        $secretaria = Role::findByName('Secretaria');

        $permissions = [
            ['admin.professors.index', 'Profesores - Ver listado de profesores',         [$superAdmin, $director]],
            ['admin.professors.edit',  'Profesores - Editar datos docentes del profesor', [$superAdmin, $director]],
            ['admin.guardians.index',  'Guardianes - Ver listado de guardianes',          [$superAdmin, $director, $secretaria]],
            ['admin.guardians.edit',   'Guardianes - Editar datos de un guardián',        [$superAdmin, $director, $secretaria]],
        ];

        foreach ($permissions as [$name, $description, $roles]) {
            $permission = Permission::firstOrCreate(
                ['name' => $name],
                ['description' => $description]
            );
            $permission->syncRoles($roles);
        }
    }
}
