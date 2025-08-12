<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MembershipDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 3; $i++) {
            DB::table('membership_details')->insert([
                'membership_id' => $i,
                'amount' => 100.00 + ($i * 10),
                'date_pay' => Carbon::now()->subDays($i),
                'date_finalization' => Carbon::now()->addMonth()->subDays($i),
                'ticket' => 'TICKET00' . $i,
                'estatus' => ($i == 1) ? 'validado' : (($i == 2) ? 'revision' : 'revision'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
