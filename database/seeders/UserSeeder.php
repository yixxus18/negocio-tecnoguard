<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = DB::table('roles')->pluck('id', 'name');

        for ($i = 1; $i <= 3; $i++) {
            DB::table('users')->insert([
                'name' => 'Guardia User ' . $i,
                'email' => 'guardia' . $i . '@example.com',
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make('password'),
                'phone' => '222222222' . $i,
                'is_active' => 1,
                'role_id' => $roles['guardia'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        for ($i = 1; $i <= 3; $i++) {
            DB::table('users')->insert([
                'name' => 'Jefe Cerrada User ' . $i,
                'email' => 'jefecerrada' . $i . '@example.com',
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make('password'),
                'phone' => '333333333' . $i,
                'is_active' => 1,
                'role_id' => $roles['jefe cerrada'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        for ($i = 1; $i <= 3; $i++) {
            DB::table('users')->insert([
                'name' => 'Jefe de Familia User ' . $i,
                'email' => 'jefedefamilia' . $i . '@example.com',
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make('password'),
                'phone' => '444444444' . $i,
                'is_active' => 1,
                'role_id' => $roles['jefe de familia'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        for ($i = 1; $i <= 3; $i++) {
            DB::table('users')->insert([
                'name' => 'Familiar User ' . $i,
                'email' => 'familiar' . $i . '@example.com',
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make('password'),
                'phone' => '555555555' . $i,
                'is_active' => 1,
                'role_id' => $roles['familiar'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
