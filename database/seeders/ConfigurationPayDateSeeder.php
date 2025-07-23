<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ConfigurationPayDateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 3; $i++) {
            DB::table('configuration_pay_date')->insert([
                'nombre_configuracion' => 'Configuración ' . $i,
                'Fecha_Corte' => $i,
                'pay' => 100 + ($i * 10),
                'tiempo_prorroga' => 5 + $i,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
