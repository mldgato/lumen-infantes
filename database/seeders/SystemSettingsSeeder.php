<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingsSeeder extends Seeder
{
    public function run(): void
    {
        SystemSetting::firstOrCreate(
            ['key' => 'enrollment_mode'],
            [
                'value' => 'direct',
                'description' => 'Modo de inscripción de estudiantes nuevos: direct (inscripción directa por el personal) o admissions (formulario público de admisiones)',
            ],
        );
    }
}
