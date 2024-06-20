<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'password' => Hash::make('123123'),
                'role_id'  => 1,
            ],
            [
                'name' => 'User',
                'email' => 'user@example.com',
                'password' => Hash::make('123'),
                'role_id' => 2, 
            ],
        ]);
    }
}
