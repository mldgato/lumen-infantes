<?php

namespace Database\Seeders;

use App\Models\Image;
use App\Models\MedicalRecord;
use App\Models\Professor;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class ProfessorSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('es_ES');

        // --- 1. CREACIÓN DE PROFESORES (IDs 1 al 13) ---
        // Los creamos primero para que hereden los IDs que tus otros seeders esperan
        $cantidadProfesores = 13;

        $this->command->info("Generando {$cantidadProfesores} profesores aleatorios...");

        for ($i = 0; $i < $cantidadProfesores; $i++) {
            $email = $faker->unique()->safeEmail;

            $user = User::factory()->create([
                'first_name' => $faker->firstName,
                'middle_name' => $faker->optional()->firstName,
                'surname' => $faker->lastName,
                'second_surname' => $faker->lastName,
                'married_surname' => $faker->optional(0.2)->lastName,
                'cui' => $faker->numerify('#############'),
                'email' => $email,
                'personal_email' => $email,
                'cellphone' => $faker->numerify('########'),
                'must_change_password' => false,
            ]);

            // Esto asegura que el ID en la tabla 'professors' coincida con el orden de creación
            Professor::create([
                'user_id' => $user->id,
            ]);

            $user->assignRole('Profesor');
            $user->image()->save(Image::factory()->make());
            MedicalRecord::create(['user_id' => $user->id]);
        }

        // --- 2. CREACIÓN DEL DIRECTOR (ID 14 en adelante) ---
        // Lo creamos al final para que no interfiera con los IDs de las asignaciones
        $this->command->info('Creando usuario Director...');

        $directorUser = User::factory()->create([
            'first_name' => $faker->firstNameMale,
            'middle_name' => $faker->firstNameMale,
            'surname' => $faker->lastName,
            'second_surname' => $faker->lastName,
            'cui' => $faker->numerify('#############'),
            'email' => 'director@lumen.net',
            'personal_email' => 'director@lumen.net',
            'cellphone' => $faker->numerify('########'),
        ]);

        $directorUser->assignRole('Director');
        $directorUser->image()->save(Image::factory()->make());
        MedicalRecord::create(['user_id' => $directorUser->id]);

        $this->command->info('¡Seeders completados! Profesores listos y Director creado al final.');
    }
}
