<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {

        $actions = [
            'read',
            'create',
            'update',
            'delete',
            'export'
        ];

        Module::all()->each(function ($module) use ($actions){

            foreach ($actions as $action){

                Permission::create([

                    'module_id'=>$module->id,

                    'name'=> ucfirst($action).' '.$module->name,

                    'slug'=> $module->slug.'.'.$action,

                    'action'=>$action

                ]);

            }

        });

    }
}
