<?php

namespace Database\Seeders;

use App\Models\LogToken;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LogTokensSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $user = User::find(random_int(1, 5));
            LogToken::create([
                'token' => random_int(100000, 999999),
                'used_at' => Carbon::now()->format('Y-m-d h-m-s'),
                'created_by' => ['name' => $user->name, 'email' => $user->email, 'id' => $user->id]
            ]);
        }
    }
}
