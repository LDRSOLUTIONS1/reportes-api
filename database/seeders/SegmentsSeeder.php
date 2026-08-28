<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SegmentsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('segments')->insert([
            [
                'name' => 'P&V',
                'description' => 'P&V',
                'estado' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'MDT&LDT',
                'description' => 'MDT&LDT',
                'estado' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'HDT',
                'description' => 'HDT',
                'estado' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'EV',
                'description' => 'EV',
                'estado' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'BACK OFFICE',
                'description' => 'BACK OFFICE',
                'estado' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
