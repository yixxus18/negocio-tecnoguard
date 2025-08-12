<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CerradaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jefeCerradaRole = DB::table('roles')->where('name', 'jefe cerrada')->first();
        $guardiaRole = DB::table('roles')->where('name', 'guardia')->first();

        if (!$jefeCerradaRole || !$guardiaRole) {
            echo "Error: 'jefe cerrada' or 'guardia' role not found. Please ensure roles are defined in your database.\n";
            return;
        }

        $jefeCerradaUsers = DB::table('users')->where('role_id', $jefeCerradaRole->id)->get();
        $guardiaUsers = DB::table('users')->where('role_id', $guardiaRole->id)->get();

        if ($jefeCerradaUsers->count() < 4 || $guardiaUsers->count() < 4) {
            echo "Warning: Not enough users with 'jefe cerrada' or 'guardia' roles to create 4 cerradas. Please ensure at least 4 users exist for each role.\n";
            // Optionally create fewer if that's desired
            return;
        }

        for ($i = 0; $i < 4; $i++) {
            DB::table('cerradas')->insert([
                'group_name' => 'Cerrada ' . ($i + 1),
                'description' => 'Descripción de la cerrada ' . ($i + 1),
                'configuration_pay_date' => ($i + 1),
                'guard_id' => $guardiaUsers->get($i)->id,
                'jefe_cerrada_id' => $jefeCerradaUsers->get($i)->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
