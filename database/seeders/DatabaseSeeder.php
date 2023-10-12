<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(UserSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(RolUserSeeder::class);
        $this->call(ProducerSeeder::class);
        $this->call(VariableSeeder::class);
        $this->call(EstanqueSeeder::class);
    }
}
