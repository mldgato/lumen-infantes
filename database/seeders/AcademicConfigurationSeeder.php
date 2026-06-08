<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AcademicConfiguration;
use App\Models\AcademicConfigurationActivity;

class AcademicConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Insertar Academic Configurations
        $configurations = [
            [
                'id' => 1,
                'year' => 2026,
                'mode' => 'free',
                'improvement_type' => 'none',
                'improvement_percentage' => null,
                'created_at' => '2026-03-12 13:22:57',
                'updated_at' => '2026-03-12 13:22:57'
            ],
        ];

        foreach ($configurations as $config) {
            AcademicConfiguration::updateOrCreate(['id' => $config['id']], $config);
        }

        // 2. Insertar Academic Configuration Activities
        $activities = [
            [
                'id' => 1,
                'academic_configuration_id' => 1,
                'activity_type_id' => 1,
                'quantity' => 6,
                'points_each' => 10.00,
                'created_at' => '2026-03-12 13:23:09',
                'updated_at' => '2026-03-12 13:23:09'
            ],
            [
                'id' => 2,
                'academic_configuration_id' => 1,
                'activity_type_id' => 4,
                'quantity' => 1,
                'points_each' => 10.00,
                'created_at' => '2026-03-12 13:23:18',
                'updated_at' => '2026-03-12 13:23:18'
            ],
            [
                'id' => 3,
                'academic_configuration_id' => 1,
                'activity_type_id' => 2,
                'quantity' => 1,
                'points_each' => 15.00,
                'created_at' => '2026-03-12 13:23:18',
                'updated_at' => '2026-03-12 13:23:18'
            ],
            [
                'id' => 4,
                'academic_configuration_id' => 1,
                'activity_type_id' => 5,
                'quantity' => 1,
                'points_each' => 10.00,
                'created_at' => '2026-03-12 13:23:18',
                'updated_at' => '2026-03-12 13:23:18'
            ],
            [
                'id' => 5,
                'academic_configuration_id' => 1,
                'activity_type_id' => 3,
                'quantity' => 1,
                'points_each' => 15.00,
                'created_at' => '2026-03-12 13:23:31',
                'updated_at' => '2026-03-12 13:23:31'
            ],
        ];

        foreach ($activities as $activity) {
            AcademicConfigurationActivity::updateOrCreate(['id' => $activity['id']], $activity);
        }
    }
}
