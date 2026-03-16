<?php

namespace Database\Seeders;

use App\Models\ActivityType;
use Illuminate\Database\Seeder;

class ActivityTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Tareas/Actividades',         'is_extra' => false],
            ['name' => 'Examen Parcial', 'is_extra' => false],
            ['name' => 'Examen Final',   'is_extra' => false],
            ['name' => 'Afectivo',       'is_extra' => false],
            ['name' => 'Actividad Extra', 'is_extra' => true],
        ];

        foreach ($types as $type) {
            ActivityType::create($type);
        }
    }
}
