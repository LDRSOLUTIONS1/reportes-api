<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SegmentsSeeder::class,
            RoleSeeder::class,
            UserSeeder::class,
            ModuleSeeder::class,
            RoleModulePermissionSeeder::class
        ]);
    }
}
