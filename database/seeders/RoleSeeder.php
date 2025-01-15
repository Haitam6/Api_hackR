<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run()
    {
        DB::table('roles')->insert([
            [
                'id' => 1,
                'nom_role' => 'Administrator',
            ],
            [
                'id' => 2,
                'nom_role' => 'User',
            ],
        ]);
    }
}
