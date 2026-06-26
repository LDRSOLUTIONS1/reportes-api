<?php

namespace Database\Seeders;

use App\Models\UserRole;
use Illuminate\Database\Seeder;

class UserRoleSeeder extends Seeder
{
    public function run(): void
    {
        UserRole::create([

            'user_id'=>1,

            'role_id'=>1

        ]);
    }
}
