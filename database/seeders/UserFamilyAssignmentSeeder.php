<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserFamilyAssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jefeFamiliaRole = DB::table('roles')->where('name', 'jefe de familia')->first();
        $familiarRole = DB::table('roles')->where('name', 'familiar')->first();

        $jefeFamiliaUsers = DB::table('users')->where('role_id', $jefeFamiliaRole->id)->get();
        $familiarUsers = DB::table('users')->where('role_id', $familiarRole->id)->get();
        $familyGroups = DB::table('family_groups')->get();

        foreach ($familyGroups as $index => $familyGroup) {

            if (isset($jefeFamiliaUsers[$index])) {
                DB::table('users')
                    ->where('id', $jefeFamiliaUsers[$index]->id)
                    ->update(['family_id' => $familyGroup->id]);
            }

            if (isset($familiarUsers[$index])) {
                DB::table('users')
                    ->where('id', $familiarUsers[$index]->id)
                    ->update(['family_id' => $familyGroup->id]);
            }
        }
    }
}
