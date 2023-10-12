<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class EstanqueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('estanques')->insert([
            'nombre'=>'Estanque A',
            'descripcion'=>'Descripción A',
            'producer_id'=>1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('estanques')->insert([
            'nombre'=>'Estanque B',
            'descripcion'=>'Descripción B',
            'producer_id'=>1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('estanques')->insert([
            'nombre'=>'Estanque C',
            'descripcion'=>'Descripción C',
            'producer_id'=>2,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('estanques')->insert([
            'nombre'=>'Estanque D',
            'descripcion'=>'Descripción D',
            'producer_id'=>2,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('estanques')->insert([
            'nombre'=>'Estanque E',
            'descripcion'=>'Descripción E',
            'producer_id'=>3,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
