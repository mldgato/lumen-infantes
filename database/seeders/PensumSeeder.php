<?php

namespace Database\Seeders;

use App\Models\Pensum;
use Illuminate\Database\Seeder;

class PensumSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['id' => 1, 'grade_id' => 1, 'year' => 2026, 'units' => 4, 'created_at' => '2026-03-12 20:10:12', 'updated_at' => '2026-03-12 20:10:12'],
            ['id' => 2, 'grade_id' => 2, 'year' => 2026, 'units' => 4, 'created_at' => '2026-03-12 20:27:12', 'updated_at' => '2026-03-12 20:27:12'],
            ['id' => 3, 'grade_id' => 3, 'year' => 2026, 'units' => 4, 'created_at' => '2026-03-12 20:27:19', 'updated_at' => '2026-03-12 20:27:19'],
            ['id' => 4, 'grade_id' => 4, 'year' => 2026, 'units' => 4, 'created_at' => '2026-03-12 20:28:00', 'updated_at' => '2026-03-12 20:28:00'],
            ['id' => 5, 'grade_id' => 5, 'year' => 2026, 'units' => 4, 'created_at' => '2026-03-12 20:41:40', 'updated_at' => '2026-03-12 20:41:40'],
            ['id' => 6, 'grade_id' => 6, 'year' => 2026, 'units' => 4, 'created_at' => '2026-03-12 20:44:37', 'updated_at' => '2026-03-12 20:44:37'],
            ['id' => 7, 'grade_id' => 7, 'year' => 2026, 'units' => 4, 'created_at' => '2026-03-12 20:47:34', 'updated_at' => '2026-03-12 20:47:34'],
            ['id' => 8, 'grade_id' => 8, 'year' => 2026, 'units' => 3, 'created_at' => '2026-03-12 20:50:34', 'updated_at' => '2026-03-12 20:50:34'],
        ];

        foreach ($data as $item) {
            Pensum::updateOrCreate(['id' => $item['id']], $item);
        }
    }
}
