<?php

namespace Database\Seeders;

use App\Models\MedicalRecord;
use App\Models\Professor;
use App\Models\Image;
use App\Models\User;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class ProfessorSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('es_ES');

        // --- 1. CREACIÓN DEL DIRECTOR ---
        $this->command->info("Creando usuario Director...");

        $directorUser = User::factory()->create([
            'first_name'      => $faker->firstNameMale,
            'middle_name'     => $faker->firstNameMale,
            'surname'         => $faker->lastName,
            'second_surname'  => $faker->lastName,
            'cui'             => $faker->numerify('#############'),
            'email'           => 'director@lumen.net', // Email fijo para fácil acceso
            'personal_email'  => 'director@lumen.net',
            'cellphone'       => $faker->numerify('########'),
        ]);

        // Asignar rol de Director
        $directorUser->assignRole('Director');

        // Relaciones polimórficas y adicionales
        $directorUser->image()->save(Image::factory()->make());
        MedicalRecord::create(['user_id' => $directorUser->id]);

        $this->command->info("Director creado: director@lumen.net");


        // --- 2. CREACIÓN DE PROFESORES ---
        $cantidadProfesores = 13;

        for ($i = 0; $i < $cantidadProfesores; $i++) {
            $email = $faker->unique()->safeEmail;

            $user = User::factory()->create([
                'first_name'      => $faker->firstName,
                'middle_name'     => $faker->optional()->firstName,
                'surname'         => $faker->lastName,
                'second_surname'  => $faker->lastName,
                'married_surname' => $faker->optional(0.2)->lastName,
                'cui'             => $faker->numerify('#############'),
                'email'           => $email,
                'personal_email'  => $email,
                'cellphone'       => $faker->numerify('########'),
            ]);

            // Vincular al modelo Professor
            Professor::create([
                'user_id' => $user->id,
            ]);

            $user->assignRole('Profesor');
            $user->image()->save(Image::factory()->make());
            MedicalRecord::create(['user_id' => $user->id]);
        }

        $this->command->info("Se han creado {$cantidadProfesores} profesores aleatorios.");
    }
}
