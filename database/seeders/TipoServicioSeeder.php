<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoServicioSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('tipo_Servicio')->insert([
            ['nombreServicio' => 'Instalacion',  'created_at' => $now, 'updated_at' => $now],
            ['nombreServicio' => 'Mantenimiento','created_at' => $now, 'updated_at' => $now],
            ['nombreServicio' => 'Revision',     'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
