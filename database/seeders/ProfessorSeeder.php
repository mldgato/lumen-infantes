<?php

namespace Database\Seeders;

use App\Models\MedicalRecord;
use App\Models\Professor;
use App\Models\Image;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProfessorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $professorsData = [
            ['first' => 'Verónica', 'middle' => null, 'surname' => 'Auyón', 'second' => 'Ocampo', 'married' => 'Galicia', 'email' => 'veroauyon@gmail.com', 'cui' => '1914907760101', 'cellphone' => '50103798'],
            ['first' => 'María', 'middle' => 'del Carmen', 'surname' => 'Castellanos', 'second' => 'Obregón', 'married' => null, 'email' => 'mcastellanosobregon@gmail.com', 'cui' => '1722961370101', 'cellphone' => '41126200'],
            ['first' => 'Eduviges', 'middle' => 'Gloria', 'surname' => 'Gómez', 'second' => 'Morales', 'married' => null, 'email' => 'gloriagomez.tareascmrn@gmail.com', 'cui' => '1617829730101', 'cellphone' => '59131879'],
            ['first' => 'Mario', 'middle' => 'Luis', 'surname' => 'Rosales', 'second' => 'Argueta', 'married' => null, 'email' => 'mariolra2006@gmail.com', 'cui' => '2357465331401', 'cellphone' => '59663161'],
            ['first' => 'Tomás', 'middle' => 'Francisco', 'surname' => 'Xon', 'second' => 'Xirum', 'married' => null, 'email' => 'teacherxon@gmail.com', 'cui' => '2461491081406', 'cellphone' => '54782164'],
            ['first' => 'Doroteo', 'middle' => null, 'surname' => 'Ajin', 'second' => 'Monroy', 'married' => null, 'email' => 'doro.ajin@gmail.com', 'cui' => '0000000000001', 'cellphone' => '00000000'],
            ['first' => 'Vicente', 'middle' => null, 'surname' => 'Culajay', 'second' => 'Marroquín', 'married' => null, 'email' => 'vicenteculajaymarroquin@gmail.com', 'cui' => '1682828360108', 'cellphone' => '59194796'],
            ['first' => 'José', 'middle' => null, 'surname' => 'Gutiérrez', 'second' => 'Coyoy', 'married' => null, 'email' => 'mjoseclemente54@gmail.com', 'cui' => '2448176930901', 'cellphone' => '56976561'],
            ['first' => 'Julio', 'middle' => 'César', 'surname' => 'Vivar', 'second' => null, 'married' => null, 'email' => 'vivarjulio1966@gmail.com', 'cui' => '1922224570108', 'cellphone' => '42712972'],
            ['first' => 'Manuel', 'middle' => null, 'surname' => 'García', 'second' => 'Pineda', 'married' => null, 'email' => 'memegae@hotmail.com', 'cui' => '2449238090101', 'cellphone' => '49091804'],
            ['first' => 'Edna', 'middle' => 'Marina', 'surname' => 'Castañaza', 'second' => null, 'married' => 'Cerrate', 'email' => 'mariferc77@gmail.com', 'cui' => '1922205862101', 'cellphone' => '59907400'],
            ['first' => 'Laura', 'middle' => 'Lily', 'surname' => 'Larios', 'second' => 'Subuyuj', 'married' => null, 'email' => 'lalilasu2@gmail.com', 'cui' => '1922539130101', 'cellphone' => '40888653'],
            ['first' => 'María', 'middle' => 'Fernanda', 'surname' => 'Ordóñez', 'second' => 'Ramos', 'married' => null, 'email' => 'mariaordonezramos823@gmail.com', 'cui' => '2327510210101', 'cellphone' => '41141602'],
        ];

        foreach ($professorsData as $data) {
            // Crear el usuario asegurando que email y personal_email sean idénticos
            $user = User::factory()->create([
                'first_name'      => $data['first'],
                'middle_name'     => $data['middle'],
                'surname'         => $data['surname'],
                'second_surname'  => $data['second'],
                'married_surname' => $data['married'],
                'cui'             => $data['cui'],
                'email'           => $data['email'],
                'personal_email'  => $data['email'],
                'cellphone'       => $data['cellphone'],
            ]);

            // Crear el profesor vinculado
            Professor::create([
                'user_id' => $user->id,
            ]);

            // Asignar el rol
            $user->assignRole('Profesor');

            // Crear imagen polimórfica
            $user->image()->save(Image::factory()->make());

            // Crear el registro médico
            MedicalRecord::create([
                'user_id' => $user->id
            ]);
        }
    }
}
