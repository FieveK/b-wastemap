<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create(['name' => 'Admin', 'email' => 'admin@bwastemap.test', 'password' => bcrypt('password'), 'role' => 'admin']);
        User::create(['name' => 'Petugas 1', 'email' => 'petugas@bwastemap.test', 'password' => bcrypt('password'), 'role' => 'petugas']);
        User::create(['name' => 'Warga', 'email' => 'warga@bwastemap.test', 'password' => bcrypt('password'), 'role' => 'masyarakat']);
    }
}
