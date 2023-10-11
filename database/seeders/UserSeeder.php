<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            'rfc'=>'FOMB770810411',
            'name'=>'JOSE BRAULIO FLORES MTZ.',
            'email'=>'jbflores24@hotmail.com',
            'password'=>'1234',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('users')->insert([
            'rfc'=>'AAAA770810411',
            'name'=>'MARIA DEL CARMEN GALLEGOS VILLALOBOS.',
            'email'=>'maria@hotmail.com',
            'password'=>'1234',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('users')->insert([
            'rfc'=>'BBBB770810411',
            'name'=>'Miguel Edgardo Flores Gallegos',
            'email'=>'miguel@hotmail.com',
            'password'=>'1234',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('users')->insert([
            'rfc'=>'CCCC770810411',
            'name'=>'José Mauricio flores Gallegos.',
            'email'=>'mauricio@gmail.com',
            'password'=>'1234',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
