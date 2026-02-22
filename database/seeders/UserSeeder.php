<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Image;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Creamos tu usuario principal para pruebas y administración
        $user = User::create([
            'cui' => '1234567890101', // Puedes poner uno real o inventado de 13 dígitos
            'first_name' => 'Manuel',
            'middle_name' => 'Lisandro',
            'surname' => 'Dardón',
            'second_surname' => 'López',
            'married_surname' => null,
            // 'name' => se llenará solo gracias al método boot() que hicimos
            'civil_status' => 'Soltero',
            'birthdate' => '1984-03-13',
            'gender' => 'Male',
            'email' => 'manueldardon@hotmail.com',
            'password' => Hash::make('password123'), // Tu contraseña de acceso
            'cellphone' => '57170018',
            'personal_email' => 'manueldardon@gmail.com',
            'address' => '9na. Calle C 2-50 Colonia Montserrat 2 zona 4 de Mixco, Guatemala',
            'is_active' => true,
        ]);
        $user->assignRole('Super Administrador');
        $user->image()->save(Image::factory()->make());
    }
}
