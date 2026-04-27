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
        // Create demo users with roles for quick testing
        User::factory()->create([
            'name' => 'Maria Cruz',
            'email' => 'maria@example.com',
            'password' => bcrypt('1234'),
            'role' => 'Cashier',
        ]);

        User::factory()->create([
            'name' => 'Daniel Reyes',
            'email' => 'daniel@example.com',
            'password' => bcrypt('1234'),
            'role' => 'Supervisor',
        ]);

        User::factory()->create([
            'name' => 'Angela Santos',
            'email' => 'angela@example.com',
            'password' => bcrypt('1234'),
            'role' => 'Administrator',
        ]);
    }
}
