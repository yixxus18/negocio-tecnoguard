<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $adminEmail = 'rs7953844@gmail.com';
        $guardiaEmail = 'yisuskroom@gmail.com';
        DB::table('users')->where('email', $adminEmail)->delete();
        DB::table('users')->where('email', $guardiaEmail)->delete();
        DB::table('users')->insert([
            'name' => 'Administrador',
            'email' => 'rs7953844@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('admin123'),
            'phone' => '8711055583',
            'is_active' => 1,
            'role_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->insert([
            'name' => 'Prueba',
            'email' => 'yisuskroom@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('admin123'),
            'phone' => '8711055582',
            'is_active' => 1,
            'role_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
