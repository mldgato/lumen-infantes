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
    }
}
