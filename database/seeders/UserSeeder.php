<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            'rfc'=>'DDDD770810411',
            'name'=>'Ana Torres Ramírez',
            'email'=>'admin@example.com',
            'password'=>Hash::make('Demo12345'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('users')->insert([
            'rfc'=>'AAAA770810411',
            'name'=>'María López Hernández',
            'email'=>'maria@example.com',
            'password'=>Hash::make('Demo12345'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('users')->insert([
            'rfc'=>'BBBB770810411',
            'name'=>'Miguel Sánchez Ortiz',
            'email'=>'miguel@example.com',
            'password'=>Hash::make('Demo12345'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('users')->insert([
            'rfc'=>'CCCC770810411',
            'name'=>'Mauricio Díaz Castro',
            'email'=>'mauricio@example.com',
            'password'=>Hash::make('Demo12345'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
