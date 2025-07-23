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

        if ($jefeCerradaUsers->isEmpty() || $guardiaUsers->isEmpty()) {
            echo "Error: No users found for 'jefe cerrada' or 'guardia' roles. Please ensure users are seeded correctly.\n";
            return;
        }

        for ($i = 0; $i < 3; $i++) {
            $jefeCerradaId = $jefeCerradaUsers->get($i)->id ?? null;
            $guardiaId = $guardiaUsers->get($i)->id ?? null;

            if (is_null($jefeCerradaId) || is_null($guardiaId)) {
                echo "Warning: Not enough 'jefe cerrada' or 'guardia' users to create 3 cerradas. Creating fewer.\n";
                break;
            }

            DB::table('cerradas')->insert([
                'group_name' => 'Cerrada ' . ($i + 1),
                'description' => 'Descripción de la cerrada ' . ($i + 1),
                'configuration_pay_date' => ($i + 1),
                'guard_id' => $guardiaId,
                'jefe_cerrada_id' => $jefeCerradaId,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
