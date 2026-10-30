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
        User::firstOrCreate(['email' => 'admin@bwastemap.test'], ['name' => 'Admin', 'password' => bcrypt('password'), 'role' => 'admin']);
        User::firstOrCreate(['email' => 'petugas@bwastemap.test'], ['name' => 'Petugas 1', 'password' => bcrypt('password'), 'role' => 'petugas']);
        User::firstOrCreate(['email' => 'warga@bwastemap.test'], ['name' => 'Warga', 'password' => bcrypt('password'), 'role' => 'masyarakat']);
    }
}
