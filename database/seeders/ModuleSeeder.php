<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [

            [
                'name' => 'Dashboard',
                'slug' => 'dashboard',
                'icon' => 'Dashboard',
                'route' => '/dashboard',
                'order' => 1,
            ],

            [
                'name' => 'Usuarios',
                'slug' => 'users',
                'icon' => 'People',
                'route' => '/users',
                'order' => 2,
            ],

            [
                'name' => 'Roles',
                'slug' => 'roles',
                'icon' => 'Security',
                'route' => '/roles',
                'order' => 3,
            ],

            [
                'name' => 'Módulos',
                'slug' => 'modules',
                'icon' => 'Apps',
                'route' => '/modules',
                'order' => 4,
            ],

            [
                'name' => 'Logs',
                'slug' => 'logs',
                'icon' => 'History',
                'route' => '/logs',
                'order' => 5,
            ],

        ];

        foreach ($modules as $module) {

            Module::create($module);
        }
    }
}
