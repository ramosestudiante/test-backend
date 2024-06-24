<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

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
                'rut' => '11111111-k',
                'birthday' =>  Carbon::createFromFormat('d/m/Y', '21/09/1995')->format('Y-m-d'),
                'address' => 'valparaiso',
                'password' => Hash::make('Password123!'),
                'role_id'  => 1,
            ],
            [
                'name' => 'User',
                'email' => 'user@example.com',
                'rut' => '11111111-k',
                'birthday' =>  Carbon::createFromFormat('d/m/Y', '21/09/1995')->format('Y-m-d'),
                'address' => 'valparaiso',
                'password' => Hash::make('Password123!'),
                'role_id' => 2, 
            ],
        ]);
    }
}
