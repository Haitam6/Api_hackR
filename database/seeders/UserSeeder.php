<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->insert([
            [
                'id' => 1,
                'name' => 'Admin',
                'email' => 'Admin@test.com',
                'role_id' => 1,
                'email_verified_at' => null,
                'password' => Hash::make('Password123@'),
                'remember_token' => null,
                'created_at' => '2025-01-15 09:11:13',
                'updated_at' => '2025-01-15 09:11:13',
            ],
        ]);
    }
}
