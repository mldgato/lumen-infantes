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
    }
}
