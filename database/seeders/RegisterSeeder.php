<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RegisterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('registers')->insert([
            'estanque_id' => 1,
            'variable_id' => 1,
            'user_id'=>2,
            'valor'=>20.1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('registers')->insert([
            'estanque_id' => 1,
            'variable_id' => 2,
            'user_id'=>3,
            'valor'=>21.1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('registers')->insert([
            'estanque_id' => 1,
            'variable_id' => 3,
            'user_id'=>3,
            'valor'=>22.1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('registers')->insert([
            'estanque_id' => 2,
            'variable_id' => 1,
            'user_id'=>1,
            'valor'=>23.1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('registers')->insert([
            'estanque_id' => 2,
            'variable_id' => 3,
            'user_id'=>4,
            'valor'=>24.1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('registers')->insert([
            'estanque_id' => 3,
            'variable_id' => 1,
            'user_id'=>2,
            'valor'=>25.1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('registers')->insert([
            'estanque_id' => 3,
            'variable_id' => 2,
            'user_id'=>1,
            'valor'=>26.1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('registers')->insert([
            'estanque_id' => 3,
            'variable_id' => 3,
            'user_id'=>3,
            'valor'=>27.1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('registers')->insert([
            'estanque_id' => 3,
            'variable_id' => 4,
            'user_id'=>1,
            'valor'=>28.1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
