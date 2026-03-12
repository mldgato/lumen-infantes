<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PensumSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['id' => 1, 'grade_id' => 1, 'year' => 2026, 'units' => 4, 'created_at' => '2026-03-12 01:54:37', 'updated_at' => '2026-03-12 01:54:37'],
            ['id' => 2, 'grade_id' => 2, 'year' => 2026, 'units' => 4, 'created_at' => '2026-03-12 02:05:53', 'updated_at' => '2026-03-12 02:05:53'],
            ['id' => 3, 'grade_id' => 3, 'year' => 2026, 'units' => 4, 'created_at' => '2026-03-12 02:06:53', 'updated_at' => '2026-03-12 02:06:53'],
        ];

        DB::table('pensums')->insert($data);
    }
}
