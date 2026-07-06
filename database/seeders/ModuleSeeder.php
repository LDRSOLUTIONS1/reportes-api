<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    /**
     * Replica 1:1 el array MODULOS que hoy tienes hardcodeado en Header.js,
     * para que la migración a base de datos no cambie nada visible en el front.
     * El campo "icon" es la clave que tu iconMap.js resolverá al componente MUI real.
     */
    public function run(): void
    {
        $modulos = [
            ['name' => 'inicio',   'title' => 'Dashboard',    'segment' => 'Inicio',   'icon' => 'dashboard',    'order' => 1],
            ['name' => 'brands',   'title' => 'Brands',       'segment' => 'Brands',   'icon' => 'sell',         'order' => 2],
            ['name' => 'projects', 'title' => 'Projects',     'segment' => 'Projects', 'icon' => 'folder',       'order' => 3],
            ['name' => 'users',    'title' => 'Users',        'segment' => 'Users',    'icon' => 'group',        'order' => 4],
            ['name' => 'roles',    'title' => 'Roles',        'segment' => 'Roles',    'icon' => 'security',     'order' => 5],
            ['name' => 'permisos', 'title' => 'Permissions',  'segment' => 'Permisos', 'icon' => 'key',          'order' => 6],
        ];

        foreach ($modulos as $modulo) {
            Module::updateOrCreate(
                ['name' => $modulo['name']],
                $modulo + ['estado' => 1]
            );
        }
    }
}
