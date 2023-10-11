<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProducerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('producers')->insert([
            'calle'=>'Donaji',
            'numero'=>13,
            'colonia'=>'Istmeña',
            'cp'=>'70680',
            'municipio'=>'Salina Cruz',
            'agencia'=>'',
            'estado'=>'Oaxaca',
            'telPrincipal'=>'9711587416',
            'telSecundario'=>'9717163242',
            'user_id'=>1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('producers')->insert([
            'calle'=>'Ruíz Cortinez',
            'numero'=>113,
            'colonia'=>'Hidalgo Poniente',
            'cp'=>'70610',
            'municipio'=>'Salina Cruz',
            'agencia'=>'',
            'estado'=>'Oaxaca',
            'telPrincipal'=>'9717144444',
            'telSecundario'=>'9717162837',
            'user_id'=>2,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('producers')->insert([
            'calle'=>'Alvaro Obregón',
            'numero'=>15,
            'colonia'=>'Refineria',
            'cp'=>'70650',
            'municipio'=>'Tehuantepec',
            'agencia'=>'',
            'estado'=>'Oaxaca',
            'telPrincipal'=>'9717143333',
            'telSecundario'=>'9717155555',
            'user_id'=>3,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('producers')->insert([
            'calle'=>'Oleoducto',
            'numero'=>215,
            'colonia'=>'Hidalgo Oriente',
            'cp'=>'70600',
            'municipio'=>'Juchitán',
            'agencia'=>'',
            'estado'=>'Oaxaca',
            'telPrincipal'=>'9717166666',
            'telSecundario'=>'9717177777',
            'user_id'=>4,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
