<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TokenAccesoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = DB::table('users')->get();

        for ($i = 1; $i <= 3; $i++) {
            DB::table('tokens_acceso')->insert([
                'usuario_id' => $users[$i - 1]->id, 
                'tipo_token' => ($i == 1) ? 'servicio' : (($i == 2) ? 'visita' : 'residente sin acceso'),
                'fecha_expiracion' => Carbon::now()->addDays(7 + $i),
                'usos' => 10 + $i,
                'valor' => 'TOKEN_00' . $i,
                'puerta' => ($i % 2 == 0) ? 'automovil' : 'peatonal',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
