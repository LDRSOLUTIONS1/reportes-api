<?php

namespace Database\Seeders;

use App\Models\Permission;  
use App\Models\RolePermission;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {

        $permissions = Permission::pluck('id');

        foreach($permissions as $permission){

            RolePermission::create([

                'role_id'=>1,

                'permission_id'=>$permission

            ]);

        }

    }
}
