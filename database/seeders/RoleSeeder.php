<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::create([
            'name'=>'Administrador',
            'description'=>'Acceso completo'
        ]);

        Role::create([
            'name'=>'Supervisor',
            'description'=>'Acceso parcial'
        ]);

        Role::create([
            'name'=>'Consulta',
            'description'=>'Solo lectura'
        ]);
    }
}
