<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;
use Carbon\Carbon;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('es_ES');

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
        for ($i = 0; $i < 40; $i++) {
            DB::table('users')->insert([
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'rut' => $faker->numerify('########-#'),
                'birthday' => $faker->date('Y-m-d', '1995-09-21'),
                'address' => $faker->address,
                'password' => Hash::make('Password123!'),
                'role_id' => rand(1, 2),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
